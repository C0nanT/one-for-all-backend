<?php

namespace Modules\TransportCard\Contracts\Repositories;

use Carbon\Carbon;
use Modules\TransportCard\Models\TransportCardBalance;

interface TransportCardBalanceRepositoryInterface
{
    public function getForDate(int $transportCardId, Carbon $date): ?TransportCardBalance;

    /**
     * @param  array<string, mixed>|null  $raw
     */
    public function upsertForDate(int $transportCardId, Carbon $date, float $balance, string $cardNumber, ?array $raw = null): TransportCardBalance;
}
