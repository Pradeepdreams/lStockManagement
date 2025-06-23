<?php

namespace App\Http\Requests\Masters;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;

class GstRegistrationTypeRequest extends FormRequest
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
        $encryptedId = $this->route('gst_registration_type');
        $id = $encryptedId ? Crypt::decryptString($encryptedId) : null;

        return [
            'name' => 'required|string|max:255|unique:gst_registration_types,name,' . $id,
            'description' => 'nullable|string',
            'code' => 'nullable|string'
        ];
    }


    public function messages(): array
    {
        return [
            'name.required' => 'The registration type name is required.',
            'name.unique' => 'This registration type name already exists. Please choose a different one.',
        ];
    }
}
