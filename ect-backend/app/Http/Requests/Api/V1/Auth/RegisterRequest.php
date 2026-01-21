<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['required','string','max:100'],
            'email' => ['required','email','max:150','unique:users,email'],
            'phone' => ['nullable','string','max:30','unique:users,phone'],
            'password' => ['required','string','min:6','confirmed'],
            'device_name' => ['required','string','max:100'],
        ];
    }
}
