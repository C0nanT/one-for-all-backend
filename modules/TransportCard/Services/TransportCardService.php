<?php

namespace Modules\TransportCard\Services;

use Carbon\Carbon;
use Modules\TransportCard\Contracts\Repositories\TransportCardBalanceRepositoryInterface;
use Modules\TransportCard\Models\TransportCard;
use Modules\TransportCard\Models\TransportCardBalance;

class TransportCardService
{
    public function __construct(
        private readonly TacomApiService $tacomApi,
        private readonly TransportCardBalanceRepositoryInterface $repository
    ) {}

    /**
     * @return array{balance: float, updated_at: string, from_cache: bool, card_number: string, last_used_at?: string|null, owner_name?: string|null}
     */
    public function getBalance(TransportCard $transportCard, bool $forceRefresh = false): array
    {
        $today = Carbon::today();

        if (!$forceRefresh) {
            $cached = $this->repository->getForDate($transportCard->id, $today);

            if ($cached !== null) {
                return $this->formatResponse($cached, true);
            }
        }

        $accessToken = $this->tacomApi->login($transportCard->username, $transportCard->password);
        $data = $this->tacomApi->findCartao($accessToken, $transportCard->card_number, $transportCard->cpf);

        $balance = $this->extractBalance($data);

        $cardNumber = $data['codExternoCartao'] ?? $transportCard->card_number ?? '';
        $cardNumber = is_string($cardNumber) ? $cardNumber : '';

        $record = $this->repository->upsertForDate(
            $transportCard->id,
            $today,
            $balance,
            $cardNumber,
            $data
        );

        return $this->formatResponse($record, false);
    }

    /**
     * @return array{balance: float, updated_at: string, from_cache: bool, card_number: string, last_used_at?: string|null, owner_name?: string|null}
     */
    private function formatResponse(TransportCardBalance $record, bool $fromCache): array
    {
        $raw = $record->raw_response;

        $ownerName = null;
        if (is_array($raw)) {
            $dependenteTitular = $raw['dependenteTitular'] ?? null;
            if (is_array($dependenteTitular)) {
                $dependente = $dependenteTitular['dependente'] ?? null;
                if (is_array($dependente) && isset($dependente['nome'])) {
                    $nome = $dependente['nome'];
                    $ownerName = is_scalar($nome) ? (string) $nome : null;
                }
            }
        }

        $dataUso = is_array($raw) ? ($raw['dataUsoCartao'] ?? null) : null;
        $lastUsedAt = is_scalar($dataUso) ? (string) $dataUso : null;

        return [
            'balance' => (float) $record->balance,
            'updated_at' => $record->updated_at instanceof Carbon ? $record->updated_at->toIso8601String() : '',
            'from_cache' => $fromCache,
            'card_number' => $record->card_number,
            'last_used_at' => $lastUsedAt,
            'owner_name' => $ownerName,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function extractBalance(array $data): float
    {
        $saldo = $data['saldo'] ?? null;

        if ($saldo === null || !is_numeric($saldo)) {
            throw new \RuntimeException('Invalid Tacom API response: missing balance');
        }

        return (float) $saldo;
    }
}
