<?php

namespace Modules\PayableAccount\Repositories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Modules\PayableAccount\Contracts\Repositories\PayableAccountNoteRepositoryInterface;
use Modules\PayableAccount\Models\PayableAccountNote;

class PayableAccountNoteRepository implements PayableAccountNoteRepositoryInterface
{
    /**
     * @return Collection<int, PayableAccountNote>
     */
    public function getByPeriod(string $period): Collection
    {
        $date = Carbon::createFromFormat('d-m-Y', $period) ?? Carbon::now();

        return PayableAccountNote::query()
            ->with(['account', 'user'])
            ->whereMonth('date', $date->month)
            ->whereYear('date', $date->year)
            ->orderByDesc('date')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): PayableAccountNote
    {
        $note = PayableAccountNote::query()->create($data);

        return $note->load(['account', 'user']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(PayableAccountNote $note, array $data): PayableAccountNote
    {
        $note->update($data);

        $updated = $note->fresh(['account', 'user']);

        return $updated ?? $note;
    }

    public function delete(PayableAccountNote $note): bool
    {
        return (bool) $note->delete();
    }
}
