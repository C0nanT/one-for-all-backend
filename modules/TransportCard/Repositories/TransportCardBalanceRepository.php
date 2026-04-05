<?php

namespace Modules\TransportCard\Repositories;

use Carbon\Carbon;
use Modules\TransportCard\Contracts\Repositories\TransportCardBalanceRepositoryInterface;
use Modules\TransportCard\Models\TransportCardBalance;

class TransportCardBalanceRepository implements TransportCardBalanceRepositoryInterface
{
    public function getForDate(int $transportCardId, Carbon $date): ?TransportCardBalance
    {
        return TransportCardBalance::query()
            ->where('transport_card_id', $transportCardId)
            ->whereDate('snapshot_date', $date)
            ->first();
    }

    /**
     * @param  array<string, mixed>|null  $raw
     */
    public function upsertForDate(int $transportCardId, Carbon $date, float $balance, string $cardNumber, ?array $raw = null): TransportCardBalance
    {
        $record = TransportCardBalance::query()
            ->where('transport_card_id', $transportCardId)
            ->whereDate('snapshot_date', $date)
            ->first();

        $data = [
            'transport_card_id' => $transportCardId,
            'snapshot_date' => $date->format('Y-m-d'),
            'balance' => $balance,
            'card_number' => $cardNumber,
            'raw_response' => $raw,
        ];

        if ($record !== null) {
            $record->update($data);

            $fresh = $record->fresh();

            return $fresh ?? $record;
        }

        return TransportCardBalance::query()->create($data);
    }
}
