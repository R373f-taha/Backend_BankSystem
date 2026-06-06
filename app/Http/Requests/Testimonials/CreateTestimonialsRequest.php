<?php

namespace App\Http\Requests\Customer;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateTestimonialsRequest extends FormRequest
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
            'testimonial_text' => 'required|string|min:10|max:5000',
            'rating' => 'required|integer|min:1|max:5',
            'type' => 'required|in:individual,business',
            'is_approved' => 'nullable|boolean',
        ];
    }
}
