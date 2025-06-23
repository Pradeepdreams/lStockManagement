<?php

namespace App\Http\Requests;

use App\Models\Vendor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Rule;

class VendorRequest extends FormRequest
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

        $encryptedId = $this->route('vendor');
        $id = $encryptedId ? Crypt::decryptString($encryptedId) : null;
        $vendorGroupId = $this->input('vendor_group_id');

        return [
            'vendor_name' => 'required|string|max:255',
            'vendor_code' => [
                'nullable',
                'string',
                'max:100',
                // Rule::unique('vendors', 'vendor_code')->ignore($id),
            ],
            'group_id' => 'nullable|integer|exists:groups,id',
            'vendor_group_id' => 'nullable|integer|exists:vendor_groups,id',
            'gst_in' => [
                'nullable',
                Rule::unique('vendors', 'gst_in')->ignore($id),
            ],

            // 'phone' => [
            //     'required',
            //     'string',
            //     'regex:/^[6-9]\d{9}$/',
            //     Rule::unique('vendors', 'phone_no')->ignore($id),
            // ],
            'phone' => [
                'required',
                'string',
                'regex:/^[6-9]\d{9}$/',
                function ($attribute, $value, $fail) use ($id, $vendorGroupId) {
                    $query = Vendor::where('phone_no', $value)
                        ->where('id', '!=', $id);

                    if ($vendorGroupId === null) {
                        // Check globally for duplicates when no group
                        $duplicate = $query->first();
                    } else {
                        // Check for duplicates in other groups
                        $duplicate = $query->where(function ($q) use ($vendorGroupId) {
                            $q->whereNull('vendor_group_id')
                                ->orWhere('vendor_group_id', '!=', $vendorGroupId);
                        })->first();
                    }

                    if ($duplicate) {
                        $fail('The phone number has already been taken by another vendor' .
                            ($vendorGroupId ? ' in a different group.' : '.'));
                    }
                },
            ],

            'email' => [
                'nullable',
                'email',
                Rule::unique('vendors', 'email')->ignore($id),
            ],
            'area_id' => 'required|integer|exists:areas,id',
            'address_line_1' => 'required|string',
            // 'address_line_2' => 'nullable|string',
            'city' => 'required',
            'state' => 'required',
            'country' => 'required',
            'pincode_id' => 'required|integer|exists:pincodes,id',
            'pan_number' => 'required|string',
            // 'payment_term_id' => 'nullable|integer|exists:payment_terms,id',
            // 'credit_days' => 'nullable|string',
            // 'credit_limit' => 'nullable|numeric|min:0',
            'gst_applicable' => 'required|boolean',
            // 'gst_applicable_from' => 'nullable|date',
            // 'gst_registration_type_id' => 'nullable|integer|exists:gst_registration_types,id',
            'tds_detail_id' => 'required|integer|exists:tds_details,id',
            // 'bank_account_no' => 'nullable|string',
            // 'ifsc_code' => 'nullable|string',
            // 'bank_name' => 'nullable|string',
            // 'bank_branch_name' => 'nullable|string',
            // 'upi_id' => 'nullable|string',
            // 'transport_facility_provided' => 'boolean',
            // 'remarks' => 'nullable|string',
            // 'reffered_source_type' => 'nullable|string',
            // 'reffered_source_id' => 'nullable|integer',
            // 'vendor_contact_details' => 'nullable|array',
            // 'vendor_contact_details.*.name' => 'required|string',
            // 'vendor_contact_details.*.phone_no' => 'nullable|string',
            // 'vendor_contact_details.*.email' => 'nullable|email',
            // 'items' => 'nullable|array',
            // 'items.*' => 'integer|exists:items,id',
        ];
    }


    protected function prepareForValidation()
    {
        $this->merge([
            'gst_applicable' => filter_var($this->gst_applicable, FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    public function withValidator($validator)
    {
        $validator->sometimes('gst_in', 'required', function ($input) {
            return $input->gst_applicable === true;
        });
    }




    public function messages(): array
    {
        return [
            'vendor_name.required' => 'Vendor name is required.',
            'vendor_name.string' => 'Vendor name must be a string.',
            'vendor_name.max' => 'Vendor name may not be greater than 255 characters.',
            'vendor_group_id.required' => 'Vendor group is required.',
            'vendor_group_id.exists' => 'Selected vendor group does not exist.',
            'area_id.required' => 'Area is required.',
            'area_id.exists' => 'Selected area does not exist.',
            'gst_in.unique' => 'This GSTIN already exists.',
            'phone.required' => 'Phone number is required.',
            'phone.unique' => 'This phone number already exists.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email address already exists.',
            'address_line_1.required' => 'Address line 1 is required.',
            'city.required' => 'City is required.',
            'state.required' => 'State is required.',
            'country.required' => 'Country is required.',
            'pincode_id.required' => 'Pincode is required.',
            'payment_term_id.integer' => 'Payment term must be an integer.',
            'credit_limit.numeric' => 'Credit limit must be a number.',
            'credit_limit.min' => 'Credit limit cannot be negative.',
            'pan_number.required' => 'PAN number is required.',
            'gst_applicable.boolean' => 'GST applicable must be true or false.',
            'gst_registration_type_id.integer' => 'GST registration type must be an integer.',
            'gst_registration_type_id.exists' => 'Selected GST registration type does not exist.',
            'tds_detail_id.required' => 'TDS detail field is required.',
            'tds_detail_id.exists' => 'Selected TDS detail does not exist.',
            'transport_facility_provided.boolean' => 'Transport facility provided must be true or false.',
            'reffered_source_type.string' => 'Referred source category must be a string.',
            'reffered_source_id.integer' => 'Referred source ID must be an integer.',
            'vendor_contact_details.array' => 'Vendor contact details must be an array.',
            'vendor_contact_details.*.name.required' => 'Each contact must have a name.',
            'vendor_contact_details.*.name.string' => 'Each contact name must be a string.',
            'vendor_contact_details.*.phone_no.string' => 'Each contact phone number must be a string.',
            'vendor_contact_details.*.email.email' => 'Each contact email must be a valid email address.',
            'items.array' => 'Items must be an array.',
            'items.*.integer' => 'Each item must be a valid integer.',
            'items.*.exists' => 'One or more selected items do not exist.',
        ];
    }
}
