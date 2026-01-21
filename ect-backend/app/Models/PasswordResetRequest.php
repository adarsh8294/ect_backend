<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PasswordResetRequest extends Model
{
    protected $fillable = [
        'user_id',
        'otp_hash',
        'otp_expires_at',
        'otp_verified_at',
        'reset_token_hash',
        'reset_token_expires_at',
        'consumed_at',
        'attempts',
    ];

    protected $casts = [
        'otp_expires_at' => 'datetime',
        'otp_verified_at' => 'datetime',
        'reset_token_expires_at' => 'datetime',
        'consumed_at' => 'datetime',
        'attempts' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}