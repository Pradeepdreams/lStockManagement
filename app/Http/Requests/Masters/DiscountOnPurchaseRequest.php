<?php

namespace App\Http\Requests\Masters;

use Illuminate\Foundation\Http\FormRequest;

class DiscountOnPurchaseRequest extends FormRequest
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
        'discount_percent' => 'required|integer|min:0|max:100',
        'applicable_date' => 'required|date',
        'discount_type' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'discount_percent.required' => 'Discount percent is required.',
            'discount_percent.integer' => 'Discount percent must be an integer.',
            'discount_percent.min' => 'Discount percent must be at least 0.',
            'discount_percent.max' => 'Discount percent must be at most 100.',

            'applicable_date.required' => 'Applicable date is required.',
            'applicable_date.date' => 'Applicable date must be a valid date.',

            'discount_type.required' => 'Discount type is required.',
            'discount_type.string' => 'Discount type must be a string.',
        ];
    }
}
