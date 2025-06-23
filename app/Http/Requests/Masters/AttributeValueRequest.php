<?php

namespace App\Http\Requests\Masters;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;

class AttributeValueRequest extends FormRequest
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
        $encryptedId = $this->route('attribute_value');
        $id = $encryptedId ? Crypt::decryptString($encryptedId) : null;

        return [
            'values' => 'required|string|max:255',
            'attribute_id' => 'required|exists:attributes,id',
        ];
    }


    public function messages(): array
    {
        return [
            'values.required' => 'The value is required.',
            'attribute_id.required' => 'Attribute is required.',
            'attribute_id.exists' => 'The selected attribute does not exist.',
        ];
    }
}
