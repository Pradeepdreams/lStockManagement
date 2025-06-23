<?php

namespace App\Http\Controllers\Admin\PurchaseEntry;

use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseEntryRequest;
use App\Models\PurchaseEntry;
use App\Services\PurchaseEntryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PurchaseEntryController extends Controller
{
    protected $service;

    public function __construct(PurchaseEntryService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        return $this->service->index($request);
    }

    public function store(PurchaseEntryRequest $request)
    {
        return $this->service->store($request->validated());
    }

    public function show($id)
    {
        return $this->service->show($id);
    }

    public function update($id, Request $request)
    {

        // dd($request);
        $decryptedId = Crypt::decryptString($id);

        $validator = Validator::make($request->all(), [
            'purchase_entry_number' => 'required|string|unique:purchase_entries,purchase_entry_number,' . $decryptedId,
            'against_po' => 'required|boolean',
            'purchase_order_id' => [
                'required_if:against_po,true',
                'nullable',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->input('against_po') && $value) {
                        if (!DB::table('purchase_orders')->where('id', $value)->exists()) {
                            $fail("The selected $attribute is invalid.");
                        }
                    }
                },
            ],
            'vendor_id' => 'required|exists:vendors,id',
            'vendor_invoice_no' => 'required|string',
            'vendor_invoice_date' => 'required|date',
            'sub_total_amount' => 'required|numeric',
            'gst_amount' => 'required|numeric',
            'total_amount' => 'required|numeric',
            'purchase_person_id' => 'required|exists:users,id',
            'mode_of_delivery' => 'required|string',
            'logistic_id' => 'required|exists:logistics,id',
            'vendor_invoice_image' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if (request()->hasFile($attribute)) {
                        $file = request()->file($attribute);
                        $mime = $file->getMimeType();
                        $validMimes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
                        if (!in_array($mime, $validMimes)) {
                            $fail("The $attribute must be a valid image or PDF.");
                        }
                    }
                },
            ],
            'remarks' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.po_item_id' => 'required|string',
            'items.*.vendor_item_name' => 'required|string',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.gst_percent' => 'required|numeric',
            'items.*.hsn_code' => 'required|string',
            'items.*.po_quantity' => 'required_if:against_po,true|numeric',
            'items.*.quantity' => 'required|numeric',
            'items.*.po_price' => 'required_if:against_po,true|numeric',
            'items.*.vendor_price' => 'required|numeric',
            // 'items.*.selling_price' => 'required|numeric',
            'items.*.sub_total_amount' => 'required|numeric',
            'items.*.total_amount' => 'required|numeric',

            'gst_details' => 'required|array|min:1',
            'gst_details.*.gst_percent' => 'required|string',
            'gst_details.*.igst_percent' => 'required|string',
            'gst_details.*.cgst_percent' => 'required|string',
            'gst_details.*.sgst_percent' => 'required|string',
            'gst_details.*.igst_amount' => 'required|string',
            'gst_details.*.cgst_amount' => 'required|string',
            'gst_details.*.sgst_amount' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        return $this->service->update($validator->validated(), $decryptedId);
    }


    public function list(){
        return $this->service->list();
    }

    public function latestEntry()
    {
        return response()->json($this->service->latestEntry());
    }

    public function pendingEntry()
    {
        return response()->json($this->service->pendingEntry());
    }

    public function approveEntry($id)
    {
        return $this->service->approveEntry($id);
    }

    public function getHistory($id)
    {
        return $this->service->getHistory($id);
    }
}
