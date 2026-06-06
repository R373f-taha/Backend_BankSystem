<?php

namespace App\Http\Requests\Customer;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return   in_array(Auth::user()?->role, ['employee', 'admin']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
$transactionId = $this->route('transaction');

        return [
         'amount' => 'sometimes|numeric|min:0.01|max:9999999999999.99',
            'type' => 'sometimes|in:credit,debit',
            'description' => 'nullable|string|max:500',
            'reference_number' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('transactions', 'reference_number')->ignore($transactionId)
            ],
            'status' => 'sometimes|in:pending,approved,rejected,completed',
            'transaction_date' => 'nullable|date',

        ];
    }
}
