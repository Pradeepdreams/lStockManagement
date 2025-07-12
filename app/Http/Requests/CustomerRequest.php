<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerRequest extends FormRequest
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
        return [
            'name' => 'required|string|max:255',
            'phone_no' => 'required|string|max:20',
            'group_id' => 'nullable|integer|exists:groups,id',
            'gst_in' => 'nullable|string|max:20',
            'pan_number' => 'nullable|string|max:10',
            'email' => 'nullable|email|max:255',
            'address_line_1' => 'nullable|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'area_id' => 'nullable|integer|exists:areas,id',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'payment_term_id' => 'nullable|integer|exists:payment_terms,id',
            'credit_days' => 'nullable|integer',
            'credit_limit' => 'nullable|numeric',
            'gst_applicable' => 'nullable|boolean',
            'gst_registration_type_id' => 'nullable|integer|exists:gst_registration_types,id',
            'tds_detail_id' => 'nullable|integer|exists:tds_details,id',
        ];
    }
}
