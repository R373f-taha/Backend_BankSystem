<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        $userId = $this->route('userId');

        return [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $userId . '|max:255', 
            'role' => 'sometimes|in:customer,employee',
            'password' => 'sometimes|string|min:8',
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'This email is already taken by another user',
            'role.in' => 'Role must be customer, employee, or admin',
            'password.min' => 'Password must be at least 8 characters',

        ];
    }
}
