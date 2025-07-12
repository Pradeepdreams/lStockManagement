<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SalesInvoiceRequest extends FormRequest
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
            'invoice_number'         => 'nullable|string|max:255',
            'invoice_date'           => 'required|date',

            'customer_id'            => 'required|exists:customers,id',
            'against_sales_order'    => 'required|boolean',
            'sales_order_id'         => 'required_if:against_sales_order,true|exists:sales_orders,id',

            'mode_of_delivery'       => 'nullable|string|max:255',
            'remarks'                => 'nullable|string',

            'sub_total'              => 'required|numeric',
            'discount'               => 'nullable|numeric',
            'discount_percent'       => 'nullable|numeric',
            'discounted_total'       => 'required|numeric',

            'igst_amount'            => 'nullable|numeric',
            'cgst_amount'            => 'nullable|numeric',
            'sgst_amount'            => 'nullable|numeric',
            'gst_total'              => 'required|numeric',

            'total_amount'           => 'required|numeric',

            'items'                  => 'required|array|min:1',
            'items.*.item_id'                => 'required|exists:items,id',
            'items.*.sales_order_item_id'   => 'nullable|exists:sales_order_items,id',
            'items.*.quantity'              => 'required|numeric|min:0',
            'items.*.item_price'            => 'required|numeric|min:0',
            'items.*.sub_total'             => 'required|numeric',

            'items.*.discount_percent'      => 'nullable|numeric|min:0|max:100',
            'items.*.discount_amount'       => 'nullable|numeric|min:0',
            'items.*.discounted_price'      => 'required|numeric|min:0',

            'items.*.gst_percent'           => 'required|numeric|min:0',
            'items.*.igst_percent'          => 'nullable|numeric|min:0',
            'items.*.cgst_percent'          => 'nullable|numeric|min:0',
            'items.*.sgst_percent'          => 'nullable|numeric|min:0',
            'items.*.igst_amount'           => 'nullable|numeric|min:0',
            'items.*.cgst_amount'           => 'nullable|numeric|min:0',
            'items.*.sgst_amount'           => 'nullable|numeric|min:0',
            'items.*.gst_amount'            => 'required|numeric|min:0',

            'items.*.total_amount'          => 'required|numeric|min:0',
            'items.*.after_discount_total'  => 'required|numeric|min:0',

            'gst_details'           => 'nullable|array',
            'gst_details.*.gst_percent'     => 'required|numeric|min:0',
            'gst_details.*.igst_percent'    => 'nullable|numeric|min:0',
            'gst_details.*.cgst_percent'    => 'nullable|numeric|min:0',
            'gst_details.*.sgst_percent'    => 'nullable|numeric|min:0',
            'gst_details.*.igst_amount'     => 'nullable|numeric|min:0',
            'gst_details.*.cgst_amount'     => 'nullable|numeric|min:0',
            'gst_details.*.sgst_amount'     => 'nullable|numeric|min:0',
        ];
    }
}
