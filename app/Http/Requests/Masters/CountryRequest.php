<?php

namespace App\Http\Requests\Masters;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;

class CountryRequest extends FormRequest
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
        $encryptedId = $this->route('country');
        $countryId = $encryptedId ? Crypt::decryptString($encryptedId) : null;

        return [
            'name' => 'required|string|max:255|unique:countries,name,' . $countryId,
            'country_code' => 'nullable|string|unique:countries,country_code'
        ];
    }


    public function messages(): array
    {
        return [
            'name.required' => 'The country name is required.',
            'name.unique' => 'This country name already exists. Please choose a different one.',
            'country_code.unique' => 'This country code already exists. Please choose a different one.',
        ];
    }
}
