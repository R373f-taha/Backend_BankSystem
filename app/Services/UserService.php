<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function changePassword(User $user, string $currentPassword, string $newPassword): bool
    {
        if (!Hash::check($currentPassword, $user->password)) {
            return false; 
        }

        $user->password = Hash::make($newPassword);
        $user->save();

        return true;
    }
    public function updateProfile(User $user, array $data): User
    {
        $user->update(array_filter([
            'name'  => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
        ]));

        return $user;
    }
}