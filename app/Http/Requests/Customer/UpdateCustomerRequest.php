<?php

namespace App\Http\Requests\Customer;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
       $customerId = $this->route('customer'); 

        return [
            'user_id' => [
                'sometimes',
                'exists:users,id',
               Rule::unique('customers', 'user_id')->ignore($customerId)
            ],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'date_of_birth' => 'nullable|date|before:today',
            'account_type' => 'sometimes|in:savings,checking',
            'account_balance' => 'nullable|numeric|min:0',
        ];
    }
}
