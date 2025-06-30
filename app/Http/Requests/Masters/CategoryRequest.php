<?php

namespace App\Http\Requests\Masters;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;

class CategoryRequest extends FormRequest
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
        $encryptedId = $this->route('category');
        $categoryId = $encryptedId ? Crypt::decryptString($encryptedId) : null;

        return [
            'name' => 'required|string|max:255|unique:categories,name,' . $categoryId,
            'description' => 'nullable|string',
            // 'margin_percent_from' => 'required|numeric|min:0|max:100',
            // 'margin_percent_to' => 'required|numeric|min:0|max:100',
            // 'gst_percent' => 'required|numeric|min:0|max:100',
            // 'gst_applicable_date' => 'required|date',
            // 'hsn_applicable_date' => 'required|date',
            // 'hsn_code' => 'required|numeric',
            // 'active_status' => 'boolean',
            // 'attributes' => 'required|array',
            // 'attributes.*' => 'exists:attributes,id'
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The category name is required.',
            'name.unique' => 'This category name already exists. Please choose a different one.',
            // 'gst_percent.required' => 'GST Percentage is required.',
            // 'gst_percent.numeric' => 'GST Percentage must be a number.',
            // 'gst_percent.min' => 'GST Percentage must be at least 0%.',
            // 'gst_percent.max' => 'GST Percentage cannot exceed 100%.',
            // 'gst_applicable_date.required' => 'Applicable date is required.',
            // 'hsn_applicable_date.required' => 'Applicable date is required.',
            // 'hsn_code.required' => 'HSN Code is required.',
            // 'attributes.required' => 'Attributes are required.',
            // 'attributes.*.exists' => 'Selected attribute does not exist.',
            // 'margin_percent_from.required' => 'Margin percent from is required.',
            // 'margin_percent_from.numeric' => 'Margin percent from must be a number.',
            // 'margin_percent_from.min' => 'Margin percent from cannot be negative.',
            // 'margin_percent_from.max' => 'Margin percent from cannot exceed 100%.',
            // 'margin_percent_to.required' => 'Margin percent to is required.',
            // 'margin_percent_to.numeric' => 'Margin percent to must be a number.',
            // 'margin_percent_to.min' => 'Margin percent to cannot be negative.',
            // 'margin_percent_to.max' => 'Margin percent to cannot exceed 100%.',
        ];
    }
}
