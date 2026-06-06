<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class TransferRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

   public function rules(): array
    {
        return [
           'receive_email' => 'required|email|exists:customers,email',
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999999999.99'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'receiver_email.required' => 'Please select a receiver.',
            'receiver_email.exists' => 'Selected receiver does not exist.',
            'receiver_email.different' => 'You cannot transfer money to yourself.',
            'amount.required' => 'Please enter transfer amount.',
            'amount.numeric' => 'Amount must be a valid number.',
            'amount.min' => 'Minimum transfer amount is $0.01.',
            'amount.max' => 'Transfer amount exceeds the allowed limit.',
            'notes.max' => 'Notes cannot exceed 500 characters.',
        ];
    }
}
