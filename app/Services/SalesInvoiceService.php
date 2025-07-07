<?php

namespace App\Services;

use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\SalesInvoiceGstDetail;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Request;
use Exception;

class SalesInvoiceService
{
    public function index(Request $request)
    {
        $search = $request->search;
        $query = SalesInvoice::query();

        if ($search) {
            $query->where('invoice_number', 'like', "%$search%")
                ->orWhere('status', 'like', "%$search%")
                ->orWhereHas('customer', function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%");
                });
        }

        $invoices = $query->with('customer', 'items.item', 'gstDetails', 'salesOrder')->latest()->paginate(10);
        return response()->json(['success' => true, 'invoices' => $invoices]);
    }

    public function store($request)
    {
        return DB::transaction(function () use ($request) {
            $invoiceNumber = $this->latestEntry();

            $request['invoice_number'] = $invoiceNumber;
            $request['created_by'] = auth()->id();
            $request['updated_by'] = auth()->id();

            $invoice = SalesInvoice::create($request);

            foreach ($request['items'] as $item) {
                if ($invoice->sales_order_id) {
                    SalesOrderItem::where('id', $item['sales_order_item_id'])->update(['invoiced_quantity' => DB::raw("invoiced_quantity + {$item['quantity']}")]);
                }

                $invoice->items()->create($item);
            }

            foreach ($request['gst_details'] ?? [] as $gst) {
                $invoice->gstDetails()->create($gst);
            }

            if ($invoice->sales_order_id) {
                $order = SalesOrder::find($invoice->sales_order_id);
                $order->status = 'Invoiced';
                $order->save();
            }

            logActivity('Created', $invoice, [$invoice]);
            return response()->json(['message' => 'Sales Invoice created successfully', 'invoice_number' => $invoiceNumber]);
        });
    }

    public function show($id)
    {
        $id = Crypt::decryptString($id);
        $invoice = SalesInvoice::with('customer', 'items.item', 'gstDetails', 'salesOrder')->findOrFail($id);
        return response()->json(['data' => $invoice]);
    }

    public function update($request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $invoice = SalesInvoice::with('items', 'gstDetails')->findOrFail($id);

            $modelChanges = $invoice->getChangedAttributesFromRequest($request);

            $itemChanges = [];
            foreach ($invoice->items as $existingItem) {
                $incomingItem = collect($request['items'])->firstWhere('item_id', $existingItem->item_id);
                if ($incomingItem) {
                    foreach (
                        Arr::only($incomingItem, [
                            'quantity',
                            'item_price',
                            'sub_total',
                            'discount_percent',
                            'discount_amount',
                            'discounted_price',
                            'gst_percent',
                            'igst_percent',
                            'cgst_percent',
                            'sgst_percent',
                            'igst_amount',
                            'cgst_amount',
                            'sgst_amount',
                            'gst_amount',
                            'total_amount',
                            'after_discount_total'
                        ]) as $key => $value
                    ) {
                        if ($existingItem->$key != $value) {
                            $itemChanges[$existingItem->item_id][$key] = [
                                'old' => $existingItem->$key,
                                'new' => $value
                            ];
                        }
                    }
                } else {
                    $itemChanges[$existingItem->item_id] = ['removed' => true];
                }
            }

            $gstChanges = [];
            foreach ($invoice->gstDetails as $index => $oldGst) {
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
                                'new' => $value
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
                'gst_details' => $gstChanges
            ];

            $request['updated_by'] = auth()->id();
            $invoice->update($request);

            $invoice->items()->delete();
            foreach ($request['items'] as $item) {
                $invoice->items()->create($item);
            }

            $invoice->gstDetails()->delete();
            foreach ($request['gst_details'] ?? [] as $gst) {
                $invoice->gstDetails()->create($gst);
            }

            if ($invoice->sales_order_id) {
                $order = SalesOrder::find($invoice->sales_order_id);
                $order->status = 'Invoiced';
                $order->save();
            }

            logActivity('Updated', $invoice, $changes);
            return response()->json(['message' => 'Sales Invoice updated successfully']);
        });
    }

    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            $id = Crypt::decryptString($id);
            $invoice = SalesInvoice::findOrFail($id);
            $invoice->items()->delete();
            $invoice->gstDetails()->delete();
            $invoice->delete();

            logActivity('Deleted', $invoice, [$invoice]);
            return response()->json(['message' => 'Sales Invoice deleted successfully']);
        });
    }

    public function latestEntry()
    {
        $datePrefix = $this->getAccountingYear();

        $lastOrder = SalesInvoice::where('purchase_entry_number', 'like', 'PI/' . $datePrefix . '/%')
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
}
