<?php

namespace App\Http\Requests\Masters;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;

class SocialMediaRequest extends FormRequest
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
        $encryptedId = $this->route('socialmedia');
        $id = $encryptedId ? Crypt::decryptString($encryptedId) : null;

        return [
            'name' => 'required|string|max:255|unique:social_media,name,' . $id,
            'links' => 'nullable|url|max:255'
        ];
    }


    public function messages(): array
    {
        return [
            'name.required' => 'The social media name is required.',
            'name.unique' => 'This social media name already exists. Please choose a different one.',
        ];
    }
}
