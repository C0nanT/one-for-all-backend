<?php

namespace Modules\TransportCard\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransportCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:80'],
            'username' => ['sometimes', 'string', 'max:255'],
            'password' => ['sometimes', 'string', 'max:255'],
            'card_number' => ['sometimes', 'string', 'max:50'],
            'cpf' => ['sometimes', 'string', 'max:14'],
        ];
    }
}
