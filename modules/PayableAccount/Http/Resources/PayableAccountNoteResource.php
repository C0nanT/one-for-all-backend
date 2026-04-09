<?php

namespace Modules\PayableAccount\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\PayableAccount\Models\PayableAccount;
use Modules\User\Models\User;

/**
 * @mixin \Modules\PayableAccount\Models\PayableAccountNote
 */
class PayableAccountNoteResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payable_account_id' => $this->payable_account_id,
            'account_name' => $this->whenLoaded('account', fn (PayableAccount $account) => $account->name),
            'user_id' => $this->user_id,
            'user_name' => $this->whenLoaded('user', fn (User $user) => $user->name),
            'text' => $this->text,
            'amount' => (float) $this->amount,
            'date' => $this->date?->format('d-m-Y'),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
