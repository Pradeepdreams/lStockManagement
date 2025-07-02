<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class PurchaseOrderRequest extends FormRequest
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
        // $id = Crypt::decryptString($this->route('purchase_order'));
        $encryptedId = $this->route('purchase_order');
        $id = $encryptedId ? Crypt::decryptString($encryptedId) : null;

        return [
            'po_number' => $isUpdate
                ? 'required|string|unique:purchase_orders,po_number,' . $id
                : 'nullable|string|unique:purchase_orders,po_number',
            'date' => 'required|date',
            'area_id' => 'required|exists:areas,id',
            'vendor_id' => 'required|exists:vendors,id',
            // 'payment_terms_id' => 'required|exists:payment_terms,id',
            // 'is_polished' => 'required|boolean',
            'mode_of_delivery' => 'required_if:is_polished,false|string',
            'expected_delivery_date' => 'required|date',
            // 'logistics' => 'required_if:mode_of_delivery,mode_of_delivery|exists:logistics,id',
            'logistics' => [
                'required_if:mode_of_delivery,mode_of_delivery',
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($this->input('mode_of_delivery') && $value) {
                        if (!DB::table('logistics')->where('id', $value)->exists()) {
                            $fail("The selected $attribute is invalid.");
                        }
                    }
                },
            ],
            'order_amount' => 'required|numeric',
            'gst_amount' => 'required|numeric',
            'total_amount' => 'required|numeric',
            'remarks' => 'nullable|string',

            'items' => $isUpdate ? 'sometimes|array' : 'required|array',
            'items.*.id' => 'nullable|exists:purchase_order_items,id',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.gst_percent' => 'required|numeric',
            'items.*.hsn_code' => 'required|numeric',
            'items.*.quantity' => 'required|numeric',
            'items.*.item_price' => 'required|numeric',
            'items.*.images' => 'sometimes|array',
            'items.*.images.*' => [
                'required',

            ],
        ];
    }


    public function messages(): array
    {
        return [
            'po_number.required' => 'Purchase Order number is required.',
            'po_number.string' => 'Purchase Order number must be a string.',
            'po_number.unique' => 'This Purchase Order number already exists.',

            'date.required' => 'Date is required.',
            'date.date' => 'Please enter a valid date.',

            'area_id.required' => 'Area is required.',
            'area_id.exists' => 'Selected area is invalid.',

            'vendor_id.required' => 'Vendor is required.',
            'vendor_id.exists' => 'Selected vendor is invalid.',

            // 'payment_terms_id.exists' => 'Selected payment term is invalid.',

            'is_polished.required' => 'polished is required.',

            'mode_of_delivery.required_if' => 'Mode of delivery is required.',

            'logistics.required_if' => 'Logistics is required.',

            'expected_delivery_date.required' => 'Expected delivery date is required.',
            'expected_delivery_date.date' => 'Expected delivery date must be a valid date.',

            'order_amount.required' => 'Order amount is required.',
            'order_amount.numeric' => 'Order amount must be a number.',

            'gst_amount.required' => 'GST amount is required.',
            'gst_amount.numeric' => 'GST amount must be a number.',

            'total_amount.required' => 'Total amount is required.',
            'total_amount.numeric' => 'Total amount must be a number.',

            'remarks.string' => 'Remarks must be a string.',

            'items.required' => 'At least one item is required.',
            'items.array' => 'Items must be an array.',

            'items.*.id.exists' => 'One or more item IDs are invalid.',
            'items.*.item_id.required' => 'Item is required.',
            'items.*.item_id.exists' => 'Selected item is invalid.',

            'items.*.gst_percent.required' => 'GST percent is required.',
            'items.*.gst_percent.numeric' => 'GST percent must be a number.',

            'items.*.hsn_code.required' => 'HSN code is required.',
            'items.*.hsn_code.numeric' => 'HSN code must be a number.',

            'items.*.quantity.required' => 'Quantity is required.',
            'items.*.quantity.numeric' => 'Quantity must be a number.',

            'items.*.item_price.required' => 'Item price is required.',
            'items.*.item_price.numeric' => 'Item price must be a number.',

            'items.*.images.array' => 'Images must be an array.',
            'items.*.images.*.required' => 'Each image is required.',
        ];
    }
}
