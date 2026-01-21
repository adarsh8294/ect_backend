<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\SendPasswordOtpRequest;
use App\Http\Requests\Api\V1\Auth\VerifyPasswordOtpRequest;
use App\Http\Requests\Api\V1\Auth\ResetPasswordRequest;
use App\Services\Auth\PasswordResetService;

class PasswordOtpController extends Controller
{
    public function sendOtp(SendPasswordOtpRequest $request, PasswordResetService $svc)
    {
        $svc->sendOtp($request->validated()['email']);

        return response()->json([
            'message' => 'If the email exists, an OTP has been sent.',
        ]);
    }

    public function verifyOtp(VerifyPasswordOtpRequest $request, PasswordResetService $svc)
    {
        $data = $request->validated();
        $resetToken = $svc->verifyOtp($data['email'], $data['otp']);

        return response()->json([
            'reset_token' => $resetToken,
            'message' => 'OTP verified.',
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request, PasswordResetService $svc)
    {
        $data = $request->validated();
        $svc->resetPassword($data['reset_token'], $data['password']);

        return response()->json([
            'message' => 'Password reset successful.',
        ]);
    }
}
