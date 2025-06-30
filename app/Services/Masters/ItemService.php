<?php

namespace App\Services\Masters;

use App\Http\Requests\Masters\ItemRequest;
use App\Models\AttributeCategory;
use App\Models\CategoryGstApplicable;
use App\Models\CategoryHsnApplicable;
use App\Models\Item;
use App\Models\ItemCategoryAttributeValue;
use App\Models\PurchaseOrderItem;
use Attribute;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Crypt;

class ItemService
{
    public function index($request)
    {
        abort_unless(auth()->user()->hasBranchPermission('view_item'), 403, 'Unauthorized');

        $search = $request->search ?? null;
        $itemQuery = Item::query();

        if ($search) {
            $itemQuery->where(function ($query) use ($search) {
                $query->where('item_name', 'like', '%' . $search . '%')
                    ->orWhere('item_code', 'like', '%' . $search . '%');
            });
        }

        $items = $itemQuery->with([
            'category',
            'activeGstPercent',
            'activeHsnCode',
            'latestGstPercent',
            'latestHsnCode'

        ])->latest()->paginate(10);
        $getLinks = $items->jsonSerialize();

        foreach ($getLinks['links'] as &$row) {

            if ($row['label'] == "Next &raquo;") {

                $row['label'] = 'Next';
            }

            if ($row['label'] == "&laquo; Previous") {

                $row['label'] = 'Previous';
            }
        }
        return response([
            'success' => true,
            'items' => $getLinks
        ]);
    }

    public function store(ItemRequest $request)
    {
        abort_unless(auth()->user()->hasBranchPermission('create_item'), 403, 'Unauthorized');

        try {
            return DB::transaction(function () use ($request) {
                $validated = $request->validated();

                $validated['item_code'] = $this->generateItemCode();

                $item = Item::create($validated);

                // Log activity
                logActivity('Created', $item, [$item]);
                $gstApplicable = [
                    "item_id" => $item->id,
                    "gst_percent" => $request['gst_percent'],
                    "applicable_date" => isset($request['gst_applicable_date'])
                        ? Carbon::parse($request['gst_applicable_date'])->format('Y-m-d')
                        : null
                ];

                $gstCreate = CategoryGstApplicable::create($gstApplicable);

                $hsnApplicable = [
                    "item_id" => $item->id,
                    "hsn_code" => $request['hsn_code'],
                    "applicable_date" => isset($request['hsn_applicable_date'])
                        ? Carbon::parse($request['hsn_applicable_date'])->format('Y-m-d')
                        : null
                ];

                $hsnCreate = CategoryHsnApplicable::create($hsnApplicable);



                return $item;
            });
        } catch (Exception $e) {
            throw new Exception("Failed to create item: " . $e->getMessage());
        }
    }

    public function show(string $encryptedId, $request)
    {
        if (!$request->po_flag) {
            abort_unless(auth()->user()->hasBranchPermission('view_item'), 403, 'Unauthorized');
        }

        $id = Crypt::decryptString($encryptedId);

        $item = Item::with([
            'category',
            'latestGstPercent',
            'latestHsnCode',
        ])->findOrFail($id);

        // return $item;

        return response()->json([
            'id' => $item->id,
            'item_name' => $item->item_name,
            'item_code' => $item->item_code,
            'category_id' => $item->category_id,
            // 'margin_percent_from' => $item->margin_percent_from,
            // 'margin_percent_to' => $item->margin_percent_to,
            'reorder_level' => $item->reorder_level,
            'unit_of_measurement' => $item->unit_of_measurement,
            'id_crypt' => $item->id_crypt,
            'category' => $item->category,
            // 'attributes' => $item->itemCategoryAttributeValues->map(function ($val) {
            //     return [
            //         'attribute_id' => $val->attributeCategory->attribute_id,
            //         'attribute_value_id' => $val->attribute_value_id,
            //     ];
            // })->values(),
        ]);
    }

    public function update(ItemRequest $request, $id)
    {
        abort_unless(auth()->user()->hasBranchPermission('edit_item'), 403, 'Unauthorized');

        try {
            return DB::transaction(function () use ($request, $id) {
                $validated = $request->validated();

                $id = Crypt::decryptString($id);

                $item = Item::findOrFail($id);
                $item->update($validated);

                // Log activity
                logActivity('Updated', $item, [$item]);

                // Always create new GST entry (not update)
                $gstApplicable = [
                    "item_id" => $item->id,
                    "gst_percent" => $request['gst_percent'],
                    "applicable_date" => isset($request['gst_applicable_date'])
                        ? Carbon::parse($request['gst_applicable_date'])->format('Y-m-d')
                        : null
                ];

                CategoryGstApplicable::create($gstApplicable);

                // Always create new HSN entry (not update)
                $hsnApplicable = [
                    "item_id" => $item->id,
                    "hsn_code" => $request['hsn_code'],
                    "applicable_date" => isset($request['hsn_applicable_date'])
                        ? Carbon::parse($request['hsn_applicable_date'])->format('Y-m-d')
                        : null
                ];

                CategoryHsnApplicable::create($hsnApplicable);

                return $item;
            });
        } catch (Exception $e) {
            throw new Exception("Failed to update item: " . $e->getMessage());
        }
    }



    public function destroy(string $encryptedId)
    {
        abort_unless(auth()->user()->hasBranchPermission('delete_item'), 403, 'Unauthorized');
        try {
            // DB::transaction(function () use ($encryptedId) {
            // return $encryptedId;

            $id = Crypt::decryptString($encryptedId);

            $item = Item::findOrFail($id);

            // return $item;
            if ($item->vendors()->count() > 0) {
                return response()->json([
                    'message' => 'Cannot delete item. It is assigned to one or more vendors.',
                ], 400);
            }
            $item->delete();
            logActivity('Deleted', $item, [$item]);
            // });

            return response()->json(['message' => 'Item Deleted successfully.']);
        } catch (Exception $e) {
            throw new Exception("Failed to delete item: " . $e->getMessage());
        }
    }


    // generate the unique item code
    public static function generateItemCode()
    {

        $lastCode = Item::where('item_code', 'LIKE', 'IT-%')
            ->orderBy('item_code', 'desc')
            ->value('item_code');
        if ($lastCode) {
            $lastNumber = (int) str_replace('IT-', '', $lastCode);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'IT-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }


    // get attribute and attribute values for edit
    public function getItemAttributeValues($request)
    {

        // return $request;
        $itemId = $request->item_id;
        // $categoryId= $request->category_id;

        $valueData = ItemCategoryAttributeValue::where('item_id', $itemId)
            ->with('attributeValue', 'attributeCategory.attribute', 'attributeCategory.category', 'item')
            ->get();

        return $valueData;
    }


    public function list($request)
    {

        // if ($request->vendor_id) {
        //     $id = Crypt::decryptString($request->vendor_id);
        //     return Item::with('category', 'vendors')
        //         ->whereHas('vendors', function ($query) use ($id) {
        //             $query->where('vendors.id', $id);
        //         })->get();
        // }
        return Item::with('category.activeGstPercent', 'category.activeHsnCode')->latest()->get();
    }


    public function poList(string $encryptedId, $request)
    {

        $id = Crypt::decryptString($encryptedId);
        $poItems = PurchaseOrderItem::with('item')->where('purchase_order_id', $id)->where('status', '0')->get();

        return $poItems;
    }
}
