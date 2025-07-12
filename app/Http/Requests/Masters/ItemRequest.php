<?php

namespace App\Http\Requests\Masters;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Rule;

class ItemRequest extends FormRequest
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

        $encryptedId = $this->route('item');
        $id = $encryptedId ? Crypt::decryptString($encryptedId) : null;

        return [
            'item_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('items', 'item_name')->ignore($id)
            ],
            'item_code' => [
                $this->isMethod('PUT') || $this->isMethod('PATCH') ? 'required' : 'nullable',
                'string',
                'max:100',
                Rule::unique('items', 'item_code')->ignore($id),
            ],
            'category_id' => 'required|exists:categories,id',
            'gst_percent' => 'required|numeric|min:0|max:100',
            'gst_applicable_date' => 'required|date',
            'hsn_applicable_date' => 'required_if:item_type,goods|date|nullable',
            'hsn_code' => 'required_if:item_type,Goods|numeric|nullable',
            'sac_code' => 'required_if:item_type,service|numeric|nullable',
            'sac_applicable_date' => 'required_if:item_type,Service|date|nullable',
            'reorder_level' => 'nullable|string',
            'unit_of_measurement' => 'required|string',
            'item_type' => 'required|string',
            'purchase_price' => 'required|numeric',
            'selling_price' => 'required|numeric',
        ];
    }



    public function messages(): array
    {
        return [
            'item_name.required' => 'Item name is required.',
            'item_name.unique' => 'This item name already exists.',
            'item_code.required' => 'Item code is required.',
            'item_code.unique' => 'This item code already exists.',
            'category_id.required' => 'Category is required.',
            'category_id.exists' => 'Selected category does not exist.',
            'unit_of_measurement.required' => 'Unit of measurement is required.',
            'gst_percent.required' => 'GST Percentage is required.',
            'gst_percent.numeric' => 'GST Percentage must be a number.',
            'gst_percent.min' => 'GST Percentage must be at least 0%.',
            'gst_percent.max' => 'GST Percentage cannot exceed 100%.',
            'gst_applicable_date.required' => 'Applicable date is required.',
            'hsn_applicable_date.required_if' => 'Applicable date is required.',
            'hsn_code.required_if' => 'HSN Code is required.',
            'sac_applicable_date.required_if' => 'Applicable date is required.',
            'sac_code.required_if' => 'SAC Code is required.',
        ];
    }
}
