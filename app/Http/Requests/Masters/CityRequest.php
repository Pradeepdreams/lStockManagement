<?php

namespace App\Http\Requests\Masters;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;

class CityRequest extends FormRequest
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
        $encryptedId = $this->route('city');
        $cityId = $encryptedId ? Crypt::decryptString($encryptedId) : null;


        return [
            'name' => 'required|string|max:255|unique:cities,name,' . $cityId,
            'state_id' => 'required|exists:states,id',
        ];
    }


    public function messages(): array
    {
        return [
            'name.required' => 'The city name is required.',
            'name.unique' => 'This city name already exists. Please choose a different one.',
            'state_id.required' => 'The state is required.',
        ];
    }
}
