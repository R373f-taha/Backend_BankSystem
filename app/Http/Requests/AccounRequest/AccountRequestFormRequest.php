<?php

namespace App\Http\Requests\AccounRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AccountRequestFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Allow all users to make this request
    }
protected function failedValidation(Validator $validator)
{
    throw new HttpResponseException(
        response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ], 422)
    );
}
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:account_requests,email|max:255',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female',
            'marital_status' => 'required|in:single,married,divorced,widowed',
            'identity_number' => 'required|string|min:8|max:50|unique:account_requests,identity_number',
            'address' => 'required|string',
            'occupation' => 'required|string|max:255',
            'deposit_amount' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'Full name is required',
            'email.required' => 'Email is required',
            'email.email' => 'Please enter a valid email',
            'date_of_birth.required' => 'Date of birth is required',
            'date_of_birth.before' => 'Date of birth must be before today',
            'gender.required' => 'Gender is required',
            'marital_status.required' => 'Marital status is required',
            'identity_number.required' => 'Identity number is required',
            'address.required' => 'Address is required',
            'occupation.required' => 'Occupation is required',
            'deposit_amount.required' => 'Deposit amount is required',
            'deposit_amount.min' => 'Deposit amount must be at least 0',
        ];
    }
}
