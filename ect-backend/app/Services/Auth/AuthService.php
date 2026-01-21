<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function attemptLogin(string $email, string $password): User
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        return $user;
    }

    public function issueToken(User $user, string $deviceName): string
    {
        // You can add abilities later if needed.
        return $user->createToken($deviceName)->plainTextToken;
    }
}
