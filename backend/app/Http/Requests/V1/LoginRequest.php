<?php

namespace App\Http\Requests\V1;

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
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            // mode: 'cookie' (web SPA, default) | 'token' (mobile + ERP, returns Sanctum PAT)
            'mode' => ['nullable', 'in:cookie,token'],
            'device_name' => ['nullable', 'string', 'max:128'],
        ];
    }
}
