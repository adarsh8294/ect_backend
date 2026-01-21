<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'reset_token' => ['required','string','min:20'],
            'password' => ['required','string','min:6','confirmed'],
        ];
    }
}
