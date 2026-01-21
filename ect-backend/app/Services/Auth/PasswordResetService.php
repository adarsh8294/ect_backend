<?php

namespace App\Services\Auth;

use App\Models\PasswordResetRequest;
use App\Models\User;
use App\Notifications\PasswordOtpNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PasswordResetService
{
    private const OTP_EXPIRES_MINUTES = 10;
    private const RESET_TOKEN_EXPIRES_MINUTES = 15;
    private const MAX_ATTEMPTS = 5;

    public function sendOtp(string $email): void
    {
        $user = User::where('email', $email)->first();

        // Prevent account enumeration: always respond "ok"
        if (!$user) {
            return;
        }

        $otp = (string) random_int(1000, 9999);
        $now = now();

        DB::transaction(function () use ($user, $otp, $now) {
            PasswordResetRequest::where('user_id', $user->id)
                ->whereNull('consumed_at')
                ->delete();

            PasswordResetRequest::create([
                'user_id' => $user->id,
                'otp_hash' => Hash::make($otp),
                'otp_expires_at' => $now->copy()->addMinutes(self::OTP_EXPIRES_MINUTES),
                'attempts' => 0,
            ]);
        });

        $user->notify(new PasswordOtpNotification($otp, self::OTP_EXPIRES_MINUTES));
    }

    public function verifyOtp(string $email, string $otp): string
    {
        $user = User::where('email', $email)->first();

        // Same anti-enumeration behavior:
        if (!$user) {
            throw ValidationException::withMessages(['otp' => ['Invalid OTP.']]);
        }

        $req = PasswordResetRequest::where('user_id', $user->id)
            ->whereNull('consumed_at')
            ->latest('id')
            ->first();

        if (!$req) {
            throw ValidationException::withMessages(['otp' => ['OTP expired or not requested.']]);
        }

        if ($req->attempts >= self::MAX_ATTEMPTS) {
            throw ValidationException::withMessages(['otp' => ['Too many attempts. Request a new OTP.']]);
        }

        if (now()->greaterThan($req->otp_expires_at)) {
            throw ValidationException::withMessages(['otp' => ['OTP expired.']]);
        }

        $req->increment('attempts');

        if (!Hash::check($otp, $req->otp_hash)) {
            throw ValidationException::withMessages(['otp' => ['Invalid OTP.']]);
        }

        $resetTokenPlain = Str::random(48);

        $req->update([
            'otp_verified_at' => now(),
            'reset_token_hash' => Hash::make($resetTokenPlain),
            'reset_token_expires_at' => now()->addMinutes(self::RESET_TOKEN_EXPIRES_MINUTES),
        ]);

        return $resetTokenPlain;
    }

    public function resetPassword(string $resetTokenPlain, string $newPassword): void
    {
        $req = PasswordResetRequest::whereNotNull('reset_token_hash')
            ->whereNull('consumed_at')
            ->latest('id')
            ->get()
            ->first(function ($row) use ($resetTokenPlain) {
                return Hash::check($resetTokenPlain, $row->reset_token_hash);
            });

        if (!$req) {
            throw ValidationException::withMessages(['reset_token' => ['Invalid reset token.']]);
        }

        if (!$req->reset_token_expires_at || now()->greaterThan($req->reset_token_expires_at)) {
            throw ValidationException::withMessages(['reset_token' => ['Reset token expired.']]);
        }

        $user = $req->user;

        DB::transaction(function () use ($user, $newPassword, $req) {
            $user->forceFill([
                'password' => Hash::make($newPassword),
            ])->save();

            // Revoke all API tokens for security
            $user->tokens()->delete();

            $req->update(['consumed_at' => now()]);
        });
    }
}
