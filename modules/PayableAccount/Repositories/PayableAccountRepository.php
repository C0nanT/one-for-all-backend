<?php

namespace Modules\PayableAccount\Repositories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Modules\PayableAccount\Contracts\Repositories\PayableAccountRepositoryInterface;
use Modules\PayableAccount\Models\PayableAccount;
use Modules\PayableAccount\Models\PayableAccountPayment;
use Modules\User\Models\User;

class PayableAccountRepository implements PayableAccountRepositoryInterface
{
    /**
     * @return Collection<int, PayableAccount>
     */
    public function getAll(string $period): Collection
    {
        $start = Carbon::parse($period)->startOfMonth()->format('Y-m-d');
        $end = Carbon::parse($period)->endOfMonth()->format('Y-m-d');

        $latestPaymentIdSubquery = PayableAccountPayment::query()
            ->selectRaw('MAX(id)')
            ->groupBy('payable_account_id', 'period');

        $query = PayableAccount::query()
            ->orderByRaw(
                'CASE WHEN EXISTS (
                    SELECT 1 FROM payable_account_payments
                    WHERE payable_account_id = payable_accounts.id
                    AND period BETWEEN ? AND ?
                    AND deleted_at IS NULL
                ) THEN 1 ELSE 0 END',
                [$start, $end]
            )
            ->orderBy('name')
            ->with([
                'payments' => function (Relation $q) use ($latestPaymentIdSubquery, $start, $end): void {
                    $q->whereIn('id', $latestPaymentIdSubquery);
                    $q->whereBetween('period', [$start, $end]);
                },
                'payments.payer',
            ]);

        return $query->get();
    }

    /**
     * @return array{month_total: float, paid_by_user: array<int, array{user_id: int, name: string, total_paid: float}>}
     */
    public function getSummary(string $period): array
    {
        $start = Carbon::parse($period)->startOfMonth()->format('Y-m-d');
        $end = Carbon::parse($period)->endOfMonth()->format('Y-m-d');

        $latestPaymentIdSubquery = PayableAccountPayment::query()
            ->selectRaw('MAX(id)')
            ->whereBetween('period', [$start, $end])
            ->groupBy('payable_account_id', 'period');

        $latestPaymentIds = PayableAccountPayment::query()
            ->whereIn('id', $latestPaymentIdSubquery)
            ->pluck('id');

        $monthTotal = (float) PayableAccountPayment::query()
            ->whereIn('id', $latestPaymentIds)
            ->sum('amount');

        $paidByUserRows = PayableAccountPayment::query()
            ->whereIn('id', $latestPaymentIds)
            ->whereNotNull('payer_id')
            ->selectRaw('payer_id, SUM(amount) as total_paid')
            ->groupBy('payer_id')
            ->orderByDesc('total_paid')
            ->get();

        $userIds = $paidByUserRows->pluck('payer_id')->unique()->filter()->values()->all();
        $users = User::query()->whereIn('id', $userIds)->get()->keyBy('id');

        $paidByUser = $paidByUserRows->map(function ($row) use ($users): array {
            $user = $users[$row->payer_id] ?? null;
            $totalPaid = $row->getAttribute('total_paid');

            return [
                'user_id' => (int) $row->payer_id,
                'name' => $user !== null ? $user->name : '-',
                'total_paid' => is_numeric($totalPaid) ? (float) $totalPaid : 0.0,
            ];
        })->values()->all();

        return [
            'month_total' => $monthTotal,
            'paid_by_user' => $paidByUser,
        ];
    }

    /**
     * @return array{paid: int, unpaid: int, paid_zero: int}
     */
    public function getPaidUnpaidPaidZeroCounts(string $period): array
    {
        $start = Carbon::parse($period)->startOfMonth()->format('Y-m-d');
        $end = Carbon::parse($period)->endOfMonth()->format('Y-m-d');

        $latestPaymentIdSubquery = PayableAccountPayment::query()
            ->selectRaw('MAX(id)')
            ->whereBetween('period', [$start, $end])
            ->groupBy('payable_account_id', 'period');

        /** @var int|string|null $paidCnt */
        $paidCnt = PayableAccountPayment::query()
            ->whereIn('id', $latestPaymentIdSubquery)
            ->where('amount', '>', 0)
            ->selectRaw('COUNT(DISTINCT payable_account_id) as cnt')
            ->value('cnt');
        $paid = (int) ($paidCnt ?? 0);

        /** @var int|string|null $paidZeroCnt */
        $paidZeroCnt = PayableAccountPayment::query()
            ->whereIn('id', $latestPaymentIdSubquery)
            ->where(function ($q): void {
                $q->where('amount', 0)->orWhereNull('amount');
            })
            ->selectRaw('COUNT(DISTINCT payable_account_id) as cnt')
            ->value('cnt');
        $paidZero = (int) ($paidZeroCnt ?? 0);

        $total = (int) PayableAccount::query()->count();
        $unpaid = max(0, $total - $paid - $paidZero);

        return [
            'paid' => $paid,
            'unpaid' => $unpaid,
            'paid_zero' => $paidZero,
        ];
    }

    public function find(int $id): ?PayableAccount
    {
        return PayableAccount::query()->find($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): PayableAccount
    {
        return PayableAccount::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(PayableAccount $payableAccount, array $data): PayableAccount
    {
        $payableAccount->update($data);

        $updated = $payableAccount->fresh();

        return $updated ?? $payableAccount;
    }

    public function delete(PayableAccount $payableAccount): bool
    {
        return (bool) $payableAccount->delete();
    }
}
