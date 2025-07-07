<?php

namespace App\Services;

use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\SalesOrderGstDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;

class SalesOrderService
{
    public function index($request)
    {
        $salesOrders =  SalesOrder::query(); //with(['items.item', 'customer', 'gstDetails', 'area', 'logistic'])->latest()->paginate(20);

        $salesOrders->with(['items.item', 'customer', 'gstDetails', 'area', 'logistic'])->latest()->paginate(20);

        $getLinks = $salesOrders->toArray();

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
            'sales_orders' => $getLinks
        ]);
    }

    public function store($request)
    {
        DB::transaction(function () use ($request, &$entry) {
            $data = $request->only((new SalesOrder)->getFillable());
            $entry = SalesOrder::create($data);

            foreach ($request->items as $item) {
                $entry->items()->create($item);
            }

            foreach ($request->gst_details ?? [] as $gst) {
                $entry->gstDetails()->create($gst);
            }

            logActivity('Created', $entry, [$entry]);
        });

        return response()->json(['message' => 'Sales Order created successfully']);
    }

    public function show($id)
    {
        $id = Crypt::decryptString($id);
        $entry = SalesOrder::with(['items.item', 'customer', 'gstDetails', 'area', 'logistic'])->findOrFail($id);
        return response()->json($entry);
    }

    public function update($request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $id = Crypt::decryptString($id);
            $entry = SalesOrder::with('items', 'gstDetails')->findOrFail($id);
            $modelChanges = $entry->getChangedAttributesFromRequest($request);

            $itemChanges = [];
            foreach ($entry->items as $oldItem) {
                $newItem = collect($request['items'] ?? [])->firstWhere('item_id', $oldItem->item_id);
                if ($newItem) {
                    foreach (
                        Arr::only($newItem, [
                            'item_id',
                            'category_id',
                            'quantity',
                            'price',
                            'discount_price',
                            'discounted_amount',
                            'total_item_price',
                            'overall_item_price',
                            'gst_percent',
                            'igst_percent',
                            'cgst_percent',
                            'sgst_percent',
                            'igst_amount',
                            'cgst_amount',
                            'sgst_amount',
                            'item_gst_amount'
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

            $entry->update($request->only($entry->getFillable()));

            $entry->items()->delete();
            foreach ($request['items'] ?? [] as $item) {
                $entry->items()->create($item);
            }

            $entry->gstDetails()->delete();
            foreach ($request['gst_details'] ?? [] as $gst) {
                $entry->gstDetails()->create($gst);
            }

            logActivity('Updated', $entry, $changes);

            return response()->json(['message' => 'Sales Order updated successfully.']);
        });
    }

    public function destroy($id)
    {
        $id = Crypt::decryptString($id);
        $entry = SalesOrder::findOrFail($id);
        $entry->delete();

        logActivity('Deleted', $entry, [$entry]);
        return response()->json(['message' => 'Sales Order deleted successfully']);
    }


    public function list()
    {
        $entries = SalesOrder::latest()->get();
        return response()->json($entries);
    }

    public function salesInvoiceList()
    {
        $entries = SalesOrder::whereIn('status', ['Delivered'])->latest()->get();
        return response()->json($entries);
    }


    public function latestPo()
    {
        $datePrefix = $this->getAccountingYear();

        $lastOrder = SalesOrder::where('sales_order_number', 'like', 'SO/' . $datePrefix . '/%')
            ->orderByDesc('id')->withTrashed()
            ->first();

        if ($lastOrder && preg_match('/SO\/' . preg_quote($datePrefix, '/') . '\/(\d+)/', $lastOrder->sales_order_number, $matches)) {
            $sequence = (int) $matches[1] + 1;
        } else {
            $sequence = 1;
        }

        $sequence = str_pad($sequence, 4, '0', STR_PAD_LEFT);

        $soNumber = 'SO/' . $datePrefix . '/' . $sequence;

        return response()->json(['sales_order_number' => $soNumber, 'sequence' => $sequence]);
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
}
