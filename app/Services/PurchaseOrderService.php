<?php

namespace App\Services;

use App\Models\Item;
use App\Models\PurchaseEntry;
use App\Models\PurchaseOrder;
use App\Models\Vendor;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PurchaseOrderService
{

    public function index($request)
    {

        abort_unless(auth()->user()->hasBranchPermission('view_purchase_order'), 403, 'Unauthorized');

        $search = $request->search ?? null;

        $poQuery = PurchaseOrder::query();

        if ($search) {
            $poQuery->where(function ($query) use ($search) {
                $query->where('po_number', 'like', '%' . $search . '%')
                    ->orWhere('po_status', 'like',  $search . '%');
            });
        }

        $po = $poQuery->with('vendor', 'purchaseItems.images', 'paymentTerms', 'area', 'purchaseItems.item.category', 'createdBy', 'updatedBy')->latest()->paginate(10);


        $getLinks = $po->toArray();

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
            'purchase_orders' => $getLinks
        ]);

        // return $po;
    }


    public function store($request)
    {

        abort_unless(auth()->user()->hasBranchPermission('create_purchase_order'), 403, 'Unauthorized');

        DB::beginTransaction();

        try {

            $poResponse = $this->latestPo();
            $poNumber = $poResponse->getData();

            $purchaseOrder = PurchaseOrder::create([
                'po_number' => $poNumber->po_number,
                'date' => Carbon::parse($request->date)->format('Y-m-d'),
                'area_id' => $request->area_id,
                'vendor_id' => $request->vendor_id,
                // 'payment_terms_id' => $request->payment_terms_id,
                'mode_of_delivery' => $request->mode_of_delivery,
                // 'is_polished' => $request->is_polished,
                'expected_delivery_date' => $request->expected_delivery_date,
                'logistic_id' => $request->logistics == "" ? null : $request->logistics,
                'po_status' => "Pending",
                'order_amount' => $request->order_amount,
                // 'minimum_discount' => $request->minimum_discount,
                'discount_amount' => $request->discount_amount,
                'discount_percent' => $request->discount_percent,
                'discounted_total' => $request->discounted_total,
                'gst_amount' => $request->gst_amount,
                'igst_amount' => $request->igst_amount ?: 0,
                'cgst_amount' => $request->cgst_amount ?: 0,
                'sgst_amount' => $request->sgst_amount ?: 0,
                'total_amount' => $request->total_amount,
                'remarks' => $request->remarks,
                'created_by' => $request->created_by ?? auth()->user()->id,
            ]);



            $purchaseGstEntries = $request->gst_entries;

            foreach ($purchaseGstEntries as $purchaseGst) {
                // return $purchaseGst;
                $purchaseOrder->purchaseOrderGst()->create([
                    'gst_percent' => $purchaseGst['gst_percent'],
                    'igst_percent' => $purchaseGst['igst_percent'],
                    'cgst_percent' => $purchaseGst['cgst_percent'],
                    'sgst_percent' => $purchaseGst['sgst_percent'],
                    'igst_amount' => $purchaseGst['igst_amount'],
                    'cgst_amount' => $purchaseGst['cgst_amount'],
                    'sgst_amount' => $purchaseGst['sgst_amount']
                ]);
            }




            $vendor = Vendor::find($purchaseOrder->vendor_id);
            foreach ($request->items as $index => $item) {
                $poItem = $purchaseOrder->purchaseItems()->create([
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'pending_quantity' => $item['quantity'],
                    'hsn_code' => $item['hsn_code'],
                    'gst_percent' => $item['gst_percent'],
                    'igst_percent' => $item['igst_percent'] ?? 0,
                    'cgst_percent' => $item['cgst_percent'] ?? 0,
                    'sgst_percent' => $item['sgst_percent'] ?? 0,
                    'igst_amount' => $item['igst_amount'] ?: 0,
                    'cgst_amount' => $item['cgst_amount'] ?: 0,
                    'sgst_amount' => $item['sgst_amount'] ?: 0,
                    'item_gst_amount' => $item['item_gst_amount'],
                    'item_price' => $item['item_price'],
                    'total_item_price' => $item['total_item_price'],
                    'discount_price' => $item['discount_price'],
                    'discounted_amount' => $item['discounted_amount'],
                    'overall_item_price' => $item['overall_item_price'],
                ]);
                $itemModel = Item::find($item['item_id']);
                $vendorCode = Str::slug($vendor->vendor_code);
                $itemName = Str::slug($itemModel->item_name);
                $timestamp = now()->format('Y-m-d_His');
                $folder = "purchase_order_images/{$vendorCode}/{$poNumber->sequence}/{$itemName}";

                $allImages = array_merge(
                    $item['images'] ?? [],
                    $item['uploaded_images'] ?? []
                );

                foreach ($allImages as $base64Image) {
                    if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                        $base64Image = substr($base64Image, strpos($base64Image, ',') + 1);
                        $extension = strtolower($type[1]);

                        if (!in_array($extension, ['jpg', 'jpeg', 'png'])) {
                            continue;
                        }

                        $decodedImage = base64_decode($base64Image);
                        if ($decodedImage === false) {
                            continue;
                        }

                        $fileName = 'img_' . $timestamp . '_' . uniqid() . '.' . $extension;
                        $filePath = storage_path("app/public/{$folder}/{$fileName}");

                        if (!File::exists(dirname($filePath))) {
                            File::makeDirectory(dirname($filePath), 0755, true);
                        }

                        file_put_contents($filePath, $decodedImage);

                        $relativePath = "{$folder}/{$fileName}";
                        $poItem->images()->create(['image' => $relativePath]);
                    }
                }
            }

            DB::commit();
            return response()->json(['message' => 'Purchase order created successfully.', 'po_number' => $poNumber->po_number]);
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Failed to restore or create Purchase Order: " . $e->getMessage());
        }
    }


    public function show($id)
    {
        abort_unless(auth()->user()->hasBranchPermission('view_purchase_order'), 403, 'Unauthorized');
        $id = Crypt::decryptString($id);
        return PurchaseOrder::with('vendor', 'purchaseOrderGst', 'purchaseItems.images', 'paymentTerms', 'area', 'purchaseItems.item.category', 'createdBy', 'updatedBy')->find($id);
    }


    public function update($request, $id)
    {
        // return $request;
        abort_unless(auth()->user()->hasBranchPermission('edit_purchase_order'), 403, 'Unauthorized');

        DB::beginTransaction();

        try {
            $id = Crypt::decryptString($id);
            $purchaseOrder = PurchaseOrder::with('purchaseItems.images')->findOrFail($id);

            $purchaseOrder->update([
                'date' =>  Carbon::parse($request->date)->format('Y-m-d'),
                'area_id' => $request->area_id,
                'vendor_id' => $request->vendor_id,
                // 'payment_terms_id' => $request->payment_terms_id,
                'mode_of_delivery' => $request->mode_of_delivery,
                // 'is_polished' => $request->is_polished,
                'expected_delivery_date' => $request->expected_delivery_date,
                'logistic_id' => $request->logistics,
                'po_status' =>  $purchaseOrder->po_status,
                'order_amount' => $request->order_amount,
                'minimum_discount' => $request->minimum_discount,
                'discount_amount' => $request->discount_amount,
                'discount_percent' => $request->discount_percent,
                'discounted_total' => $request->discounted_total,
                'gst_amount' => $request->gst_amount,
                'igst_amount' => $request->igst_amount ?: 0,
                'cgst_amount' => $request->cgst_amount ?: 0,
                'sgst_amount' => $request->sgst_amount ?: 0,
                'total_amount' => $request->total_amount,
                'remarks' => $request->remarks,
                'updated_by' => $request->updated_by ?? auth()->user()->id,
            ]);

            $purchaseOrder->purchaseOrderGst()->delete();

            $purchaseGstEntries = $request->gst_entries;

            foreach ($purchaseGstEntries as $purchaseGst) {
                $purchaseOrder->purchaseOrderGst()->create([
                    'gst_percent' => $purchaseGst['gst_percent'],
                    'igst_percent' => $purchaseGst['igst_percent'],
                    'cgst_percent' => $purchaseGst['cgst_percent'],
                    'sgst_percent' => $purchaseGst['sgst_percent'],
                    'igst_amount' => $purchaseGst['igst_amount'],
                    'cgst_amount' => $purchaseGst['cgst_amount'],
                    'sgst_amount' => $purchaseGst['sgst_amount']
                ]);
            }

            $vendor = Vendor::findOrFail($purchaseOrder->vendor_id);
            $poNumber = (object)['sequence' => explode('/', $purchaseOrder->po_number)[2]];
            $existingPoItems = $purchaseOrder->purchaseItems->keyBy('id');

            $requestItemIds = [];

            foreach ($request->items as $item) {

                $id = isset($item['id']) ? $item['id'] : null;
                if ($id) {
                    $requestItemIds[] = $id;
                }

                if ($id && $existingPoItems->has($id)) {

                    $poItem = $existingPoItems[$id];
                    $poItem->update([
                        'item_id' => $item['item_id'],
                        'quantity' => $item['quantity'],
                        'hsn_code' => $item['hsn_code'],
                        'gst_percent' => $item['gst_percent'],
                        'igst_percent' => $item['igst_percent'] ?? 0,
                        'cgst_percent' => $item['cgst_percent'] ?? 0,
                        'sgst_percent' => $item['sgst_percent'] ?? 0,
                        'igst_amount' => $item['igst_amount'] ?: 0,
                        'cgst_amount' => $item['cgst_amount'] ?: 0,
                        'sgst_amount' => $item['sgst_amount'] ?: 0,
                        'item_gst_amount' => $item['item_gst_amount'],
                        'item_price' => $item['item_price'],
                        'total_item_price' => $item['total_item_price'],
                        'discount_price' => $item['discount_price'],
                        'discounted_amount' => $item['discounted_amount'],
                        'overall_item_price' => $item['overall_item_price'],
                    ]);
                } else {

                    $poItem = $purchaseOrder->purchaseItems()->create([
                        'item_id' => $item['item_id'],
                        'quantity' => $item['quantity'],
                        'hsn_code' => $item['hsn_code'],
                        'gst_percent' => $item['gst_percent'],
                        'igst_percent' => $item['igst_percent'] ?? 0,
                        'cgst_percent' => $item['cgst_percent'] ?? 0,
                        'sgst_percent' => $item['sgst_percent'] ?? 0,
                        'igst_amount' => $item['igst_amount'] ?? 0.00,
                        'cgst_amount' => $item['cgst_amount'] ?? 0.00,
                        'sgst_amount' => $item['sgst_amount'] ?? 0.00,
                        'item_gst_amount' => $item['item_gst_amount'],
                        'item_price' => $item['item_price'],
                        'total_item_price' => $item['total_item_price'],
                        'discount_price' => $item['discount_price'],
                        'discounted_amount' => $item['discounted_amount'],
                        'overall_item_price' => $item['overall_item_price'],
                    ]);
                }

                $existingImages = $poItem->images->pluck('image')->toArray();
                $providedImages = $item['images'] ?? [];
                $uploadedImages = $item['uploaded_images'] ?? [];

                $newBase64Images = [];
                $retainedUrls = [];


                foreach ($providedImages as $img) {
                    if (preg_match('/^data:image\/(\w+);base64,/', $img)) {
                        $newBase64Images[] = $img;
                    } else {

                        $relative = str_replace('\/', '/', $img);

                        $retainedUrls[] = $relative;
                    }
                }


                $imagesToDelete = array_diff($existingImages, $retainedUrls);

                foreach ($imagesToDelete as $imagePath) {
                    Storage::disk('public')->delete($imagePath);
                    $poItem->images()->where('image', $imagePath)->delete();
                }


                $allImages = array_merge($newBase64Images, $uploadedImages);
                $itemModel = Item::find($item['item_id']);
                $vendorCode = Str::slug($vendor->vendor_code);
                $itemName = Str::slug($itemModel->item_name);
                $timestamp = now()->format('Y-m-d_His');
                $folder = "purchase_order_images/{$vendorCode}/{$poNumber->sequence}/{$itemName}";

                foreach ($allImages as $base64Image) {
                    if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                        $base64Image = substr($base64Image, strpos($base64Image, ',') + 1);
                        $extension = strtolower($type[1]);

                        if (!in_array($extension, ['jpg', 'jpeg', 'png'])) {
                            continue;
                        }

                        $decodedImage = base64_decode($base64Image);
                        if ($decodedImage === false) {
                            continue;
                        }

                        $fileName = 'img_' . $timestamp . '_' . uniqid() . '.' . $extension;
                        $filePath = storage_path("app/public/{$folder}/{$fileName}");

                        if (!File::exists(dirname($filePath))) {
                            File::makeDirectory(dirname($filePath), 0755, true);
                        }

                        file_put_contents($filePath, $decodedImage);
                        $relativePath = "{$folder}/{$fileName}";
                        $poItem->images()->create(['image' => $relativePath]);
                    }
                }
            }


            $existingIds = $existingPoItems->keys()->toArray();
            $toDeleteIds = array_diff($existingIds, array_filter($requestItemIds));

            foreach ($toDeleteIds as $deleteId) {
                $poItem = $existingPoItems[$deleteId];
                foreach ($poItem->images as $image) {
                    Storage::disk('public')->delete($image->image);
                    $image->delete();
                }
                $poItem->delete();
            }

            DB::commit();
            return response()->json(['message' => 'Purchase order updated successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception("Failed to update Purchase Order: " . $e->getMessage());
        }
    }



    public function destroy($encryptedId)
    {

        abort_unless(auth()->user()->hasBranchPermission('delete_purchase_order'), 403, 'Unauthorized');
        try {
            // return DB::transaction(function () use ($encryptedId) {
            $id = Crypt::decryptString($encryptedId);
            $po = PurchaseOrder::findOrFail($id);
            $purchaseEntries = PurchaseEntry::where('purchase_order_id', $po->id)->get();
            if ($purchaseEntries->count() > 0) {
                return response()->json(['message' => 'Cannot delete Purchase Order. It is assigned to one or more purchase entries.'], 400);
            }
            $po->delete();
            logActivity('Deleted', $po, [$po]);
            return response()->json(['message' => 'Purchase Order deleted successfully.']);
            // });
        } catch (Exception $e) {
            throw new Exception("Failed to delete Purchase Order: " . $e->getMessage());
        }
    }


    public function list()
    {
        return PurchaseOrder::get();
    }


    public function latestPo()
    {
        $datePrefix = $this->getAccountingYear();

        $lastOrder = PurchaseOrder::where('po_number', 'like', 'PO/' . $datePrefix . '/%')
            ->orderByDesc('id')->withTrashed()
            ->first();

        if ($lastOrder && preg_match('/PO\/' . preg_quote($datePrefix, '/') . '\/(\d+)/', $lastOrder->po_number, $matches)) {
            $sequence = (int) $matches[1] + 1;
        } else {
            $sequence = 1;
        }

        $sequence = str_pad($sequence, 4, '0', STR_PAD_LEFT);

        $poNumber = 'PO/' . $datePrefix . '/' . $sequence;

        return response()->json(['po_number' => $poNumber, 'sequence' => $sequence]);
    }


    // accounting year
    public function getAccountingYear(): string
    {
        $now = now();

        $year = $now->year;
        $startOfFY = Carbon::create($year, 4, 1);

        if ($now->lt($startOfFY)) {
            $startYear = $year - 1;
            $endYear = $year;
        } else {
            $startYear = $year;
            $endYear = $year + 1;
        }

        return $startYear . '-' . substr($endYear, -2);
    }


    public function pendingPo()
    {
        return PurchaseOrder::where('po_status', '!=', 'Completed')->get();
    }

    public function vendorPendingPo($id)
    {
        $id = Crypt::decryptString($id);
        return PurchaseOrder::where('vendor_id', $id)->where('po_status', '!=', 'Completed')->get();
    }
}
