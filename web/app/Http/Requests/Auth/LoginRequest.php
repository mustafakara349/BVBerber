<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:150'],
            'password' => ['required', 'string', 'min:6'],
            'remember' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'E-posta adresi alanı zorunludur.',
            'email.email' => 'Geçerli bir e-posta adresi giriniz.',
            'email.max' => 'E-posta adresi en fazla 150 karakter olabilir.',
            'password.required' => 'Şifre alanı zorunludur.',
            'password.string' => 'Şifre metin formatında olmalıdır.',
            'password.min' => 'Şifre en az 6 karakter uzunluğunda olmalıdır.',
        ];
    }
}
