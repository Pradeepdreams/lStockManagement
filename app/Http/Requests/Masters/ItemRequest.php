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
            // 'margin_percent_from' => 'required|numeric|min:0|max:100',
            // 'margin_percent_to' => 'required|numeric|min:0|max:100',
            'reorder_level' => 'nullable|string',
            'unit_of_measurement' => 'required|string',
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
            // 'margin_percent_from.required' => 'Margin percent from is required.',
            // 'margin_percent_from.numeric' => 'Margin percent from must be a number.',
            // 'margin_percent_from.min' => 'Margin percent from cannot be negative.',
            // 'margin_percent_from.max' => 'Margin percent from cannot exceed 100%.',
            // 'margin_percent_to.required' => 'Margin percent to is required.',
            // 'margin_percent_to.numeric' => 'Margin percent to must be a number.',
            // 'margin_percent_to.min' => 'Margin percent to cannot be negative.',
            // 'margin_percent_to.max' => 'Margin percent to cannot exceed 100%.',
            'unit_of_measurement.required' => 'Unit of measurement is required.',
        ];
    }
}
