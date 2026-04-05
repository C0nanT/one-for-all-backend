<?php

namespace Modules\PayableAccount\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\User\Models\User;

/**
 * @property \Carbon\Carbon|null $date
 */
class PayableAccountNote extends Model
{
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'payable_account_id',
        'user_id',
        'text',
        'amount',
        'date',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'date' => 'date',
        ];
    }

    /**
     * @return BelongsTo<PayableAccount, PayableAccountNote>
     */
    public function account(): BelongsTo
    {
        /** @phpstan-ignore-next-line return.type */
        return $this->belongsTo(PayableAccount::class, 'payable_account_id');
    }

    /**
     * @return BelongsTo<User, PayableAccountNote>
     */
    public function user(): BelongsTo
    {
        /** @phpstan-ignore-next-line return.type */
        return $this->belongsTo(User::class, 'user_id');
    }
}
