<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Services\Auth\AuthService;

class LoginController extends Controller
{
    public function __invoke(LoginRequest $request, AuthService $auth)
    {
        $data = $request->validated();

        $user = $auth->attemptLogin($data['email'], $data['password']);
        $user->forceFill(['last_login_at' => now()])->save();

        $user->load('wallet');

        $token = $auth->issueToken($user, $data['device_name']);

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ]);
    }

    public function me()
    {
        $user = request()->user()->load('wallet');

        return response()->json([
            'user' => new UserResource($user),
        ]);
    }
}
