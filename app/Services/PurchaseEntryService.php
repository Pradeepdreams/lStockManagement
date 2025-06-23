<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Employee;
use App\Models\PurchaseEntry;
use App\Models\PurchaseEntryItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class PurchaseEntryService
{

    public function index($request)
    {
        abort_unless(auth()->user()->hasBranchPermission('view_purchase_entry'), 403, 'Unauthorized');

        $search = $request->search ?? null;

        $poQuery = PurchaseEntry::query();

        if ($search) {
            $poQuery->where(function ($query) use ($search) {
                $query->where('purchase_entry_number', 'like', '%' . $search . '%')
                    ->orWhere('status', 'like', '%' . $search . '%')
                    ->orWhere('vendor_invoice_no', 'like', '%' . $search . '%')
                    ->orWhereHas('vendor', function ($vendorQuery) use ($search) {
                        $vendorQuery->where('vendor_name', 'like', '%' . $search . '%');
                    });
            });
        }

        $entries = $poQuery->with('vendor', 'items', 'gstDetails', 'purchaseOrder', 'purchasePerson', 'logistic', 'createdBy', 'updatedBy')->latest()->paginate(10);

        $getLinks = $entries->toArray();

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
            'purchase_entries' => $getLinks
        ]);
    }

    public function store($request)
    {
        abort_unless(auth()->user()->hasBranchPermission('create_purchase_entry'), 403, 'Unauthorized');

        try {

            return DB::transaction(function () use ($request) {
                $PiNumberData = $this->latestEntry();
                $piNumber = $PiNumberData->getData();

                $request['purchase_entry_number'] = $piNumber->purchase_entry_number;

                if (isset($request['vendor_invoice_image']) && $request['vendor_invoice_image'] instanceof UploadedFile) {
                    $fileExtension = $request['vendor_invoice_image']->getClientOriginalExtension();
                    $fileName = $request['vendor_invoice_no'] . '.' . $fileExtension;

                    $purchaseEntryNo = str_replace('/', '_', $piNumber->purchase_entry_number);
                    $folderPath = 'purchase_entry_images/' . $purchaseEntryNo;

                    $path = $request['vendor_invoice_image']->storeAs($folderPath, $fileName, 'public');

                    $request['vendor_invoice_image'] = $path;
                }

                $request['created_by'] = auth()->user()->id;
                $request['vendor_invoice_date'] = Carbon::parse($request['vendor_invoice_date'])->format('Y-m-d');
                $request['purchase_order_id'] = $request['against_po'] == 1 ? $request['purchase_order_id'] : null;

                $entry = PurchaseEntry::create($request);

                $itemCount = 0;
                $approvalStatus = false;
                foreach ($request['items'] ?? [] as $item) {
                    // return $item;

                    if ($request['against_po'] == 1) {
                        $poItem = PurchaseOrderItem::where('id', $item['po_item_id'])->where('purchase_order_id', $request['purchase_order_id'])
                            ->where('item_id', $item['item_id'])
                            ->first();

                        if ($item['quantity'] > $poItem->pending_quantity || $item['vendor_price'] > $poItem->item_price) {
                            $approvalStatus = true;
                        }

                        $poItem->inward_quantity = $poItem->inward_quantity + $item['quantity'];
                        $poItem->pending_quantity = max(0, $poItem->pending_quantity - $item['quantity']);
                        $poItem->item_status = $poItem->pending_quantity == 0 ? 1 : 0;
                        $itemCount += $poItem->pending_quantity;
                        $poItem->save();
                    }


                    $entry->items()->create($item);
                }

                if ($request['against_po'] == 1) {
                    $order = PurchaseOrder::find($request['purchase_order_id']);
                    if ($itemCount == 0) {
                        $order->po_status = 'Completed';
                        $order->save();
                    } else {
                        $order->po_status = 'Partially Pending';
                        $order->save();
                    }
                }


                foreach ($request['gst_details'] ?? [] as $gst) {
                    $entry->gstDetails()->create($gst);
                }

                if ($approvalStatus) {
                    $entry->status = "Pending Approval";
                    $entry->save();
                } else {
                    $entry->status = "Direct";
                    $entry->save();
                }

                logActivity('Created', $entry, [$entry]);
                return response()->json(['message' => 'Purchase Entry created successfully.', 'purchase_entry_number' => $piNumber->purchase_entry_number]);
            });
        } catch (Exception $e) {
            throw new Exception("Failed to restore or create Purchase Entry: " . $e->getMessage());
        }
    }


    public function show($id)
    {
        abort_unless(auth()->user()->hasBranchPermission('view_purchase_entry'), 403, 'Unauthorized');

        try {
            $id = Crypt::decryptString($id);
            return PurchaseEntry::with('vendor', 'items.item.category.attributes.attribute_values', 'gstDetails', 'purchaseOrder', 'purchasePerson', 'logistic', 'createdBy', 'updatedBy')->findOrFail($id);
        } catch (Exception $e) {
            throw new Exception('message: ' . $e->getMessage());
        }
    }


    public function update($request, $id)
    {
        abort_unless(auth()->user()->hasBranchPermission('update_purchase_entry'), 403, 'Unauthorized');

        // try {
        return DB::transaction(function () use ($request, $id) {
            // $id = Crypt::decryptString($id);
            $entry = PurchaseEntry::with('items', 'gstDetails')->findOrFail($id);

            $modelChanges = $entry->getChangedAttributesFromRequest($request);

            $itemChanges = [];
            foreach ($entry->items as $index => $oldItem) {
                $newItem = collect($request['items'] ?? [])->firstWhere('item_id', $oldItem->item_id);
                if ($newItem) {
                    foreach (
                        Arr::only($newItem, [
                            'vendor_item_name',
                            'po_item_id',
                            'item_id',
                            'gst_percent',
                            'hsn_code',
                            'po_quantity',
                            'quantity',
                            'po_price',
                            'vendor_price',
                            // 'selling_price',
                            'sub_total_amount',
                            'total_amount'
                        ]) as $key => $value
                    ) {
                        if ($oldItem->$key != $value) {
                            $itemChanges[$oldItem->item_id][$key] = [
                                'old' => $oldItem->$key,
                                'new' => $value,
                            ];
                        }
                    }
                } else {
                    $itemChanges[$oldItem->item_id] = ['removed' => true];
                }
            }



            // return $entry->gstDetails;

            $gstChanges = [];
            foreach ($entry->gstDetails as $index => $oldGst) {
                $newGst = $request['gst_details'][$index] ?? null;
                if ($newGst) {
                    foreach (
                        Arr::only($newGst, [
                            'gst_percent',
                            'igst_percent',
                            'cgst_percent',
                            'sgst_percent',
                            'igst_amount',
                            'cgst_amount',
                            'sgst_amount'
                        ]) as $key => $value
                    ) {
                        if ($oldGst->$key != $value) {
                            $gstChanges[$oldGst->id][$key] = [
                                'old' => $oldGst->$key,
                                'new' => $value,
                            ];
                        }
                    }
                } else {
                    $gstChanges[$index] = ['removed' => true];
                }
            }



            $changes = [
                'model' => $modelChanges,
                'items' => $itemChanges,
                'gst_details' => $gstChanges,
            ];

            if (isset($request['vendor_invoice_image']) && $request['vendor_invoice_image'] instanceof UploadedFile) {
                $fileExtension = $request['vendor_invoice_image']->getClientOriginalExtension();
                $fileName = $request['vendor_invoice_no'] . '.' . $fileExtension;

                $purchaseEntryNo = str_replace('/', '_', $entry->purchase_entry_number);
                $folderPath = 'purchase_entry_images/' . $purchaseEntryNo;

                $path = $request['vendor_invoice_image']->storeAs($folderPath, $fileName, 'public');
                $request['vendor_invoice_image'] = $path;
            }

            $request['updated_by'] = auth()->user()->id;
            $entry->update($request);

            if ($entry->against_po == 1) {
                foreach ($entry->items as $oldItem) {
                    $poItem = PurchaseOrderItem::where('id', $oldItem->po_item_id)->where('purchase_order_id', $entry->purchase_order_id)
                        ->where('item_id', $oldItem->item_id)
                        ->first();

                    if ($poItem) {
                        $poItem->inward_quantity = max(0, $poItem->inward_quantity - $oldItem->quantity);
                        $poItem->pending_quantity = $poItem->pending_quantity + $oldItem->quantity;
                        $poItem->item_status = $poItem->pending_quantity == 0 ? 1 : 0;
                        $poItem->save();
                    }
                }
            }

            $entry->items()->delete();

            $itemCount = 0;
            $approvalStatus = false;

            foreach ($request['items'] ?? [] as $item) {
                // return $item;
                if ($request['against_po'] == 1) {
                    $poItem = PurchaseOrderItem::where('id', $item['po_item_id'])->where('purchase_order_id', $request['purchase_order_id'])
                        ->where('item_id', $item['item_id'])
                        ->first();

                    if ($item['quantity'] > $poItem->pending_quantity || $item['vendor_price'] > $poItem->item_price) {
                        $approvalStatus = true;
                    }

                    $poItem->inward_quantity = $poItem->inward_quantity + $item['quantity'];
                    $poItem->pending_quantity = max(0, $poItem->pending_quantity - $item['quantity']);
                    $poItem->item_status = $poItem->pending_quantity == 0 ? 1 : 0;
                    $itemCount += $poItem->pending_quantity;
                    $poItem->save();
                }

                $entry->items()->create($item);
            }

            if ($request['against_po'] == 1) {
                $order = PurchaseOrder::find($request['purchase_order_id']);
                $order->po_status = $itemCount == 0 ? 'Completed' : 'Partially Pending';
                $order->save();
            }

            $entry->gstDetails()->delete();
            foreach ($request['gst_details'] ?? [] as $gst) {
                $entry->gstDetails()->create($gst);
            }

            $entry->status = $approvalStatus ? 'Pending Approval' : 'Direct';
            $entry->save();

            logActivity('Updated', $entry, $changes);

            return response()->json(['message' => 'Purchase Entry updated successfully.']);
        });
        // } catch (Exception $e) {
        //     throw new Exception("Failed to update Purchase Entry: " . $e->getMessage());
        // }
    }


    public function list()
    {
        return PurchaseEntry::where('status', '!=', 'Pending Approval')->with('vendor', 'items', 'gstDetails', 'purchaseOrder', 'purchasePerson', 'logistic', 'createdBy', 'updatedBy')->get();
    }


    public function latestEntry()
    {
        $datePrefix = $this->getAccountingYear();

        $lastOrder = PurchaseEntry::where('purchase_entry_number', 'like', 'PI/' . $datePrefix . '/%')
            ->orderByDesc('id')->withTrashed()
            ->first();

        if ($lastOrder && preg_match('/PI\/' . preg_quote($datePrefix, '/') . '\/(\d+)/', $lastOrder->purchase_entry_number, $matches)) {
            $sequence = (int) $matches[1] + 1;
        } else {
            $sequence = 1;
        }

        $sequence = str_pad($sequence, 4, '0', STR_PAD_LEFT);

        $piNumber = 'PI/' . $datePrefix . '/' . $sequence;

        return response()->json(['purchase_entry_number' => $piNumber, 'sequence' => $sequence]);
    }

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


    public function pendingEntry()
    {
        $pendings = PurchaseEntry::with('vendor', 'items', 'gstDetails', 'purchaseOrder', 'purchasePerson', 'logistic', 'createdBy', 'updatedBy')
            ->where('status', 'Pending Approval')->latest()->paginate(10);

        $getLinks = $pendings->toArray();

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
            'pending_entries' => $getLinks
        ]);
    }


    public function approveEntry($id)
    {
        // abort_unless(auth()->user()->hasBranchPermission('approve_purchase_entry'), 403, 'Unauthorized');

        try {
            $id = Crypt::decryptString($id);
            $entry = PurchaseEntry::findOrFail($id);
            $entry->status = "Approved";
            $entry->updated_by = auth()->user()->id;
            $entry->save();
            return response()->json(['message' => 'Purchase Entry Approved successfully.']);
        } catch (Exception $e) {
            throw new Exception('message: ' . $e->getMessage());
        }
    }


    public function getHistory($id)
    {
        $id = Crypt::decryptString($id);

        $entry = ActivityLog::where('model', 'PurchaseEntry')->where('model_id', $id)->with('user')->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $entry,
        ]);
    }
}
