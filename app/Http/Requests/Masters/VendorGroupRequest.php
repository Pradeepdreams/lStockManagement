<?php

namespace App\Http\Requests\Masters;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;

class VendorGroupRequest extends FormRequest
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
        $encryptedId = $this->route('vendor_group');
        $id = $encryptedId ? Crypt::decryptString($encryptedId) : null;

        return [
            'name' => 'required|string|max:255|unique:vendor_groups,name,' . $id,
            'description' => 'nullable|string'
        ];
    }


    public function messages(): array
    {
        return [
            'name.required' => 'The vendor group name is required.',
            'name.unique' => 'This vendor group name already exists. Please choose a different one.',
        ];
    }
}
