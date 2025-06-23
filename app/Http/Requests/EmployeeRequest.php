<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class EmployeeRequest extends FormRequest
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
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:10',
            'gender' => 'required',
            'date_of_join' => 'nullable|date',
            'qualification_id' => 'nullable|exists:qualifications,id',
            'is_active' => 'boolean',
            'salary' => 'nullable|string',
            'address_line_1' => 'required|string',
            'address_line_2' => 'nullable|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'country' => 'required|string',
            'pincode_id' => 'required|exists:pincodes,id',
            'password' => [
                Rule::requiredIf($this->isMethod('post')),
                'string'
            ],
            'branches' => ['required', 'array', 'min:1'],
            'branches.*.branch_id' => ['required', 'integer', 'distinct', 'exists:branches,id'],
            'branches.*.role_ids' => ['required', 'array', 'min:1'],
            'branches.*.role_ids.*' => ['required', 'integer', 'exists:roles,id'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->toArray();

        $customErrors = [];

        foreach ($errors as $key => $messages) {
            if (str_starts_with($key, 'branches.') && str_contains($key, '.branch_id')) {
                $customErrors['branch_id'] = $messages;
            } elseif (str_starts_with($key, 'branches.') && str_contains($key, '.role_ids')) {
                $customErrors['role_ids'] = $messages;
            } else {
                $customErrors[$key] = $messages;
            }
        }

        throw new HttpResponseException(
            response()->json(['errors' => $customErrors], 422)
        );
    }


    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required.',
            'first_name.string' => 'First name must be a string.',
            'first_name.max' => 'First name must not exceed 255 characters.',

            'last_name.string' => 'Last name must be a string.',
            'last_name.max' => 'Last name must not exceed 255 characters.',

            'email.required' => 'Email is required.',
            'email.email' => 'Email must be a valid email address.',

            'phone.required' => 'Phone number is required.',
            'phone.string' => 'Phone number must be a string.',
            'phone.max' => 'Phone number must not exceed 10 characters.',

            'gender.required' => 'Gender is required.',
            'date_of_join.date' => 'Date of join must be a valid date.',

            'qualification_id.exists' => 'Selected qualification is invalid.',

            'is_active.boolean' => 'Active status must be true or false.',
            'salary.string' => 'Salary must be a string.',

            'address_line_1.required' => 'Address Line 1 is required.',
            'address_line_1.string' => 'Address Line 1 must be a string.',
            'address_line_2.string' => 'Address Line 2 must be a string.',

            'city.required' => 'City is required.',
            'city.string' => 'City must be a string.',
            'state.required' => 'State is required.',
            'state.string' => 'State must be a string.',
            'country.required' => 'Country is required.',
            'country.string' => 'Country must be a string.',

            'pincode_id.required' => 'Pincode is required.',
            'pincode_id.exists' => 'Selected pincode is invalid.',

            'password.required' => 'Password is required.',
            'password.string' => 'Password must be a string.',

            'branches.required' => 'At least one branch must be selected.',
            'branches.array' => 'Branches must be an array.',

            'branches.*.branch_id.required' => 'Branch is required.',
            'branches.*.branch_id.integer' => 'Branch ID must be an integer.',
            'branches.*.branch_id.distinct' => 'Duplicate branch IDs are not allowed.',
            'branches.*.branch_id.exists' => 'Selected branch does not exist.',

            'branches.*.role_ids.required' => 'Role is required.',
            'branches.*.role_ids.array' => 'Roles must be an array.',
            'branches.*.role_ids.*.required' => 'Each role ID is required.',
            'branches.*.role_ids.*.integer' => 'Each role ID must be an integer.',
            'branches.*.role_ids.*.exists' => 'Selected role does not exist.',
        ];
    }
}
