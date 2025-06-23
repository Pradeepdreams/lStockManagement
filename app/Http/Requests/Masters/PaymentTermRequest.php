<?php

namespace App\Http\Requests\Masters;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;

class PaymentTermRequest extends FormRequest
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

        $encryptedId = $this->route('payment_term');
        $id = $encryptedId ? Crypt::decryptString($encryptedId) : null;


        return [
            'name' => 'required|string|max:255|unique:payment_terms,name,' . $id,
            'description' => 'nullable|string|max:500',
            'active_status' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Payment term name is required.',
            'name.unique' => 'This payment term already exists.',
        ];
    }
}
