<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreateCustomerByEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && in_array(Auth::user()->role, ['employee', 'admin']);;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|unique:users,email|unique:account_requests,email',
            'password'        => 'nullable|string|min:6',
            'date_of_birth'   => 'required|date|before:-18 years',
            'address'         => 'required|string|max:500',

            'identity_number' => 'required|string|max:50|unique:account_requests,identity_number',
            'gender'          => 'required|in:male,female',
            'marital_status'  => 'required|in:single,married,divorced,widowed',
            'occupation'      => 'nullable|string|max:255',

            'balance'         => 'nullable|numeric|min:0',
        ];
    }
}
