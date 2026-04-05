<?php

namespace Modules\TransportCard\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransportCardRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:80'],
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
            'card_number' => ['required', 'string', 'max:50'],
            'cpf' => ['required', 'string', 'max:14'],
        ];
    }
}
