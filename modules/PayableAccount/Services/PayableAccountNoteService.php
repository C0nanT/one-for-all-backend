<?php

namespace Modules\PayableAccount\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\PayableAccount\Contracts\Repositories\PayableAccountNoteRepositoryInterface;
use Modules\PayableAccount\Models\PayableAccountNote;

class PayableAccountNoteService
{
    public function __construct(
        private readonly PayableAccountNoteRepositoryInterface $repository
    ) {}

    /**
     * @return Collection<int, PayableAccountNote>
     */
    public function list(string $period): Collection
    {
        return $this->repository->getByPeriod($period);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): PayableAccountNote
    {
        return $this->repository->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(PayableAccountNote $note, array $data): PayableAccountNote
    {
        return $this->repository->update($note, $data);
    }

    public function delete(PayableAccountNote $note): bool
    {
        return $this->repository->delete($note);
    }
}
