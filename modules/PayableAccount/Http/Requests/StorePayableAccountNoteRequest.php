<?php

namespace Modules\PayableAccount\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePayableAccountNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'payable_account_id' => ['required', 'integer', 'exists:payable_accounts,id'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'text' => ['required', 'string', 'max:1000'],
            'amount' => ['required', 'numeric', 'min:0'],
            'date' => ['required', 'date'],
        ];
    }
}
