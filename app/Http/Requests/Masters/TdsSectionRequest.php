<?php

namespace App\Http\Requests\Masters;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;

class TdsSectionRequest extends FormRequest
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
        $encryptedId = $this->route('tds_section');
        $id = $encryptedId ? Crypt::decryptString($encryptedId) : null;

        return [
            'name' => 'required|string|max:255|unique:tds_sections,name,' . $id,
            'percent_with_pan' => 'required|numeric|min:0|max:100',
            'percent_without_pan' => 'required|numeric|min:0|max:100',
            'applicable_date' => 'required|date',
            'amount_limit' => 'required|numeric|min:0',
        ];
    }


    public function messages(): array
    {
        return [
            'name.required' => 'The tds section name is required.',
            'name.unique' => 'This tds section name already exists. Please choose a different one.',
            'applicable_date.required' => 'Applicable date is required.',
            'percent_with_pan.required' => 'Percentage with PAN is required.',
            'percent_without_pan.required' => 'Percentage without PAN is required.',
            'amount_limit.required' => 'Amount limit is required.',
        ];
    }
}
