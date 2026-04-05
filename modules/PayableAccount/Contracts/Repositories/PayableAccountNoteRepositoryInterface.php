<?php

namespace Modules\PayableAccount\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\PayableAccount\Models\PayableAccountNote;

interface PayableAccountNoteRepositoryInterface
{
    /**
     * @return Collection<int, PayableAccountNote>
     */
    public function getByPeriod(string $period): Collection;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): PayableAccountNote;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(PayableAccountNote $note, array $data): PayableAccountNote;

    public function delete(PayableAccountNote $note): bool;
}
