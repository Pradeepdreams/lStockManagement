<?php

namespace App\Http\Requests\Masters;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;

class StateRequest extends FormRequest
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
        $encryptedId = $this->route('state');
        $stateId = $encryptedId ? Crypt::decryptString($encryptedId) : null;

        return [
            'name' => 'required|string|max:255|unique:states,name,' . $stateId,
            'country_id' => 'required|exists:countries,id',
        ];
    }


    public function messages(): array
    {
        return [
            'name.required' => 'The state name is required.',
            'name.unique' => 'This state name already exists. Please choose a different one.',
            'country_id.required' => 'The country is required.',
        ];
    }
}
