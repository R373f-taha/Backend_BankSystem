<?php

namespace App\Http\Requests\Customer;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateTestimonialsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return  Auth::user()?->role === 'admin';;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        return [
            'testimonial_text' => 'sometimes|required|string|min:10|max:5000',
            'rating' => 'sometimes|required|integer|min:1|max:5',
            'type' => 'sometimes|required|in:individual,business',
            'is_approved' => 'sometimes|boolean',
        ];
    }
}
