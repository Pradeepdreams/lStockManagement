<?php

namespace App\Http\Requests\Masters;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;

class TdsDetailRequest extends FormRequest
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
        $encryptedId = $this->route('tds_detail');
        $id = $encryptedId ? Crypt::decryptString($encryptedId) : null;

        return [
            'name' => 'required|string|max:255' . $id,
            'description' => 'nullable|string',
            'active_status' => 'boolean',
            'tds_section_id' => 'required|exists:tds_sections,id'
        ];
    }
}
