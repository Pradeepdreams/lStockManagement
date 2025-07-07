<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SalesOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Sales Order main fields
            'sales_order_number' => 'nullable|string|max:50',
            'order_date' => 'required|date',
            'customer_id' => 'required|exists:customers,id',
            'igst_amount' => 'nullable|numeric',
            'cgst_amount' => 'nullable|numeric',
            'sgst_amount' => 'nullable|numeric',
            'gst_amount' => 'nullable|numeric',
            'order_amount' => 'nullable|numeric',
            'total_amount' => 'nullable|numeric',
            'discount' => 'nullable|numeric',
            'discount_percent' => 'nullable|numeric',
            'discount_amount' => 'nullable|numeric',
            'discounted_total' => 'nullable|numeric',
            'payment_terms_id' => 'nullable|exists:payment_terms,id',
            'mode_of_delivery' => 'nullable|string|max:100',
            'expected_delivery_date' => 'nullable|date',
            'logistic_id' => 'nullable|exists:logistics,id',
            'sales_status' => 'nullable|string|max:50',
            'remarks' => 'nullable|string',
            'created_by' => 'nullable|exists:users,id',
            'updated_by' => 'nullable|exists:users,id',

            // Sales Order GST Details
            'gst_details' => 'nullable|array',
            'gst_details.*.sales_order_id' => 'nullable|exists:sales_orders,id',
            'gst_details.*.gst_percent' => 'required|numeric',
            'gst_details.*.igst_percent' => 'nullable|numeric',
            'gst_details.*.cgst_percent' => 'nullable|numeric',
            'gst_details.*.sgst_percent' => 'nullable|numeric',
            'gst_details.*.igst_amount' => 'nullable|numeric',
            'gst_details.*.cgst_amount' => 'nullable|numeric',
            'gst_details.*.sgst_amount' => 'nullable|numeric',

            // Sales Order Items
            'items' => 'required|array|min:1',
            'items.*.sales_order_id' => 'nullable|exists:sales_orders,id',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.invoiced_quantity' => 'nullable|numeric|min:0',
            'items.*.pending_quantity' => 'nullable|numeric|min:0',
            'items.*.hsn_code' => 'nullable|string|max:20',
            'items.*.gst_percent' => 'nullable|numeric',
            'items.*.igst_percent' => 'nullable|numeric',
            'items.*.cgst_percent' => 'nullable|numeric',
            'items.*.sgst_percent' => 'nullable|numeric',
            'items.*.igst_amount' => 'nullable|numeric',
            'items.*.cgst_amount' => 'nullable|numeric',
            'items.*.sgst_amount' => 'nullable|numeric',
            'items.*.item_gst_amount' => 'nullable|numeric',
            'items.*.item_price' => 'required|numeric',
            'items.*.total_item_price' => 'nullable|numeric',
            'items.*.discount_price' => 'nullable|numeric',
            'items.*.discounted_amount' => 'nullable|numeric',
            'items.*.overall_item_price' => 'nullable|numeric',
        ];
    }
}
