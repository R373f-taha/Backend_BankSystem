<?php

namespace App\Http\Requests\Customer;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateTransactionRequest extends FormRequest
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
        return [
             'customer_id' => 'required|exists:customers,id',
            'amount' => 'required|numeric|min:0.01|max:9999999999999.99',
            'type' => 'required|in:credit,debit',
            'description' => 'nullable|string|max:500',
            'reference_number' => 'nullable|string|max:50|unique:transactions,reference_number',
            'status' => 'nullable|in:pending,approved,rejected,completed',
            'transaction_date' => 'nullable|date',
        ];
    }


    protected function prepareForValidation(): void
    {
   
        if (!$this->has('status')) {
            $this->merge(['status' => 'pending']);
        }


        if (!$this->has('transaction_date')) {
            $this->merge(['transaction_date' => now()]);
        }
    }
}
