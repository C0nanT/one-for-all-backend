<?php

namespace Modules\TransportCard\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property array<string, mixed>|null $raw_response
 * @property \Carbon\Carbon|null $updated_at
 * @property int $transport_card_id
 */
class TransportCardBalance extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'transport_card_id',
        'snapshot_date',
        'balance',
        'card_number',
        'raw_response',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'snapshot_date' => 'date',
            'balance' => 'decimal:2',
            'raw_response' => 'array',
        ];
    }

    /**
     * @return BelongsTo<TransportCard, $this>
     */
    public function transportCard(): BelongsTo
    {
        return $this->belongsTo(TransportCard::class);
    }
}
