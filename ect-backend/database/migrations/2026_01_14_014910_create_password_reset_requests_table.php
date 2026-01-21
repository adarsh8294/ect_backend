<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('password_reset_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('otp_hash');
            $table->timestamp('otp_expires_at');

            $table->timestamp('otp_verified_at')->nullable();

            $table->string('reset_token_hash')->nullable();
            $table->timestamp('reset_token_expires_at')->nullable();

            $table->timestamp('consumed_at')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);

            $table->timestamps();

            $table->index(['user_id', 'otp_expires_at']);
            $table->index(['user_id', 'reset_token_expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_requests');
    }
};