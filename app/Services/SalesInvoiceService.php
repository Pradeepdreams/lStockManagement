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
        // return response()->json(['success' => true, 'invoices' => $invoices]);
        $getLinks = $invoices->toArray();

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
            'sales_invoices' => $getLinks
        ]);
    }

    public function store($request)
    {
        return DB::transaction(function () use ($request) {

            $invoiceNumber = $this->latestEntry();
            $request['invoice_number'] = $invoiceNumber;
            $request['created_by'] = auth()->id();

            $invoice = SalesInvoice::create($request);


            foreach ($request['items'] as $itemData) {

                $invoiceItem = $invoice->items()->create($itemData);

                if (!empty($itemData['sales_order_item_id']) && $request['against_sales_order'] == 1) {
                    $soItem = SalesOrderItem::find($itemData['sales_order_item_id']);

                    if ($soItem) {
                        $soItem->invoiced_quantity += $itemData['quantity'];
                        $soItem->pending_quantity = max(0, $soItem->ordered_quantity - $soItem->invoiced_quantity);
                        $soItem->status = $soItem->pending_quantity == 0 ? 1 : 0;
                        $soItem->save();
                    }
                }
            }

            if (!empty($request['sales_order_id']) && $request['against_sales_order'] == 1) {
                $salesOrder = SalesOrder::with('items')->find($request['sales_order_id']);

                if ($salesOrder) {
                    $allInvoiced = $salesOrder->items->every(function ($item) {
                        return $item->pending_quantity == 0;
                    });

                    $salesOrder->status = $allInvoiced ? 'Invoiced' : 'Partially Invoiced';
                    $salesOrder->save();
                }
            }

            foreach ($request['gst_details'] ?? [] as $gst) {
                $invoice->gstDetails()->create($gst);
            }

            logActivity('Created', $invoice, [$invoice]);

            return response()->json([
                'message' => 'Sales Invoice created successfully',
                'invoice_number' => $invoiceNumber
            ]);
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
            $invoice = SalesInvoice::with('items')->findOrFail($id);

            $modelChanges = $invoice->getChangedAttributesFromRequest($request);

            foreach ($invoice->items as $oldItem) {
                if ($oldItem->sales_order_item_id) {
                    $soItem = SalesOrderItem::find($oldItem->sales_order_item_id);
                    if ($soItem) {
                        $soItem->invoiced_quantity = max(0, $soItem->invoiced_quantity - $oldItem->quantity);
                        $soItem->pending_quantity = max(0, $soItem->ordered_quantity - $soItem->invoiced_quantity);
                        $soItem->status = $soItem->pending_quantity == 0;
                        $soItem->save();
                    }
                }
            }

            $invoice->items()->delete();
            $invoice->gstDetails()->delete();


            foreach ($request['items'] as $item) {
                $invoiceItem = $invoice->items()->create($item);

                if (!empty($item['sales_order_item_id'])) {
                    $soItem = SalesOrderItem::find($item['sales_order_item_id']);

                    if ($soItem) {
                        $soItem->invoiced_quantity += $item['quantity'];
                        $soItem->pending_quantity = max(0, $soItem->ordered_quantity - $soItem->invoiced_quantity);
                        $soItem->status = $soItem->pending_quantity == 0;
                        $soItem->save();
                    }
                }
            }

            foreach ($request['gst_details'] ?? [] as $gst) {
                $invoice->gstDetails()->create($gst);
            }

            if (!empty($request['sales_order_id'])) {
                $salesOrder = SalesOrder::with('items')->find($request['sales_order_id']);

                if ($salesOrder) {
                    $allInvoiced = $salesOrder->items->every(function ($item) {
                        return $item->pending_quantity == 0;
                    });

                    $salesOrder->status = $allInvoiced ? 'Invoiced' : 'Partially Invoiced';
                    $salesOrder->save();
                }
            }

            $request['updated_by'] = auth()->id();
            $invoice->update($request);

            $changes = [
                'model' => $modelChanges,
                'items' => [],
                'gst_details' => []
            ];

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

        $lastOrder = SalesInvoice::where('invoice_number', 'like', 'SI/' . $datePrefix . '/%')
            ->orderByDesc('id')->withTrashed()
            ->first();

        if ($lastOrder && preg_match('/SI\/' . preg_quote($datePrefix, '/') . '\/(\d+)/', $lastOrder->purchase_entry_number, $matches)) {
            $sequence = (int) $matches[1] + 1;
        } else {
            $sequence = 1;
        }

        $sequence = str_pad($sequence, 4, '0', STR_PAD_LEFT);

        $siNumber = 'SI/' . $datePrefix . '/' . $sequence;

        return response()->json(['invoice_number' => $siNumber, 'sequence' => $sequence]);
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
