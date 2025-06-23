<?php

namespace App\Http\Requests\Masters;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;

class AreaRequest extends FormRequest
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

        $encryptedId = $this->route('area');
        $id = $encryptedId ? Crypt::decryptString($encryptedId) : null;

        return [
            'name' => 'required|string|max:255|unique:areas,name,' . $id,
            // 'area_code' => 'required|string|max:20|unique:areas,area_code,' . $id
        ];
    }


    public function messages(): array
    {
        return [
            'name.required' => 'The area name is required.',
            'name.unique' => 'This area name already exists. Please choose a different one.',
            // 'area_code.required' => 'The area code is required.',
            // 'area_code.unique' => 'This area code already exists. Please choose a different one.',
        ];
    }
}
