<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UnlinkGoogleAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * 解除綁定前，須確認帳號仍保有其他登入方式（ADR-004）。
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $user = $this->user();

            if (blank($user->google_id)) {
                $validator->errors()->add('google', '此帳號尚未綁定 Google。');
            }

            if (blank($user->password)) {
                $validator->errors()->add('password', '請先設定密碼後再解除 Google 綁定，以保留登入方式。');
            }
        });
    }
}
