<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class PurchaseEntryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isUpdate = $this->method() === 'PUT' || $this->method() === 'PATCH';

        $id = null;
        try {
            $encryptedId = $this->route('id');
            if ($encryptedId) {
                $id = Crypt::decryptString($encryptedId);
            }
        } catch (\Exception $e) {
            $id = null;
        }

        return [
            'purchase_entry_number' => $isUpdate
                ? 'required|string|unique:purchase_entries,purchase_entry_number,' . $id
                : 'nullable|string|unique:purchase_entries,purchase_entry_number',

            'against_po' => 'required|boolean',
            'purchase_order_id' => [
                'required_if:against_po,true',
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($this->input('against_po') && $value) {
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
            // 'vendor_invoice_image' => 'nullable|mimes:jpeg,png,jpg,pdf|max:2048',
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
            'items.*.po_item_id' => 'required|exists:purchase_order_items,id',
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

        ];
    }


    public function messages()
    {
        return [
            'purchase_entry_number.required' => 'The purchase entry number is required.',
            'purchase_entry_number.unique' => 'The purchase entry number must be unique.',
            'against_po.required' => 'The against PO is required.',
            'purchase_order_id.required_if' => 'The purchase order is required.',
            'purchase_order_id.exists' => 'The selected purchase order is invalid.',
            'vendor_id.required' => 'The vendor is required.',
            'vendor_id.exists' => 'The selected vendor is invalid.',
            'vendor_invoice_no.required' => 'The vendor invoice number is required.',
            'vendor_invoice_date.required' => 'The vendor invoice date is required.',
            'vendor_invoice_date.date' => 'The vendor invoice date must be a valid date.',
            'sub_total_amount.required' => 'The subtotal amount is required.',
            'gst_amount.required' => 'The GST amount is required.',
            'total_amount.required' => 'The total amount is required.',
            'purchase_person_id.required' => 'The purchase person name is required.',
            'mode_of_delivery.required' => 'The mode of delivery is required.',
            'logistic_id.required' => 'The logistics information is required.',

            'items.required' => 'At least one item is required.',
            'items.array' => 'The items must be an array.',
            // 'items.*.po_item_id.required' => 'The PO item details are required.',
            'items.*.vendor_item_name.required' => 'The vendor item name is required.',
            'items.*.item_id.required' => 'The item details are required.',
            'items.*.item_id.exists' => 'The selected item is invalid.',
            'items.*.gst_percent.required' => 'The GST percent is required.',
            'items.*.hsn_code.required' => 'The HSN code is required.',
            'items.*.po_quantity.required_if' => 'The PO quantity is required.',
            'items.*.quantity.required' => 'The quantity is required.',
            'items.*.po_price.required_if' => 'The PO price is required.',
            'items.*.vendor_price.required' => 'The vendor price is required.',
            // 'items.*.selling_price.required' => 'The selling price is required.',
            'items.*.sub_total_amount.required' => 'The item subtotal amount is required.',
            'items.*.total_amount.required' => 'The item total amount is required.',

            'gst_details.required' => 'At least one GST detail is required.',
            'gst_details.array' => 'The GST details must be an array.',
            'gst_details.*.gst_percent.required' => 'The GST percent in GST details is required.',
            'gst_details.*.igst_percent.required' => 'The IGST percent is required.',
            'gst_details.*.cgst_percent.required' => 'The CGST percent is required.',
            'gst_details.*.sgst_percent.required' => 'The SGST percent is required.',
            'gst_details.*.igst_amount.required' => 'The IGST amount is required.',
            'gst_details.*.cgst_amount.required' => 'The CGST amount is required.',
            'gst_details.*.sgst_amount.required' => 'The SGST amount is required.',
        ];
    }
}
