<?php

namespace Modules\PayableAccount\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\PayableAccount\Http\Requests\StorePayableAccountNoteRequest;
use Modules\PayableAccount\Http\Requests\UpdatePayableAccountNoteRequest;
use Modules\PayableAccount\Http\Resources\PayableAccountNoteResource;
use Modules\PayableAccount\Models\PayableAccountNote;
use Modules\PayableAccount\Services\PayableAccountNoteService;

class PayableAccountNoteController extends Controller
{
    public function __construct(
        private readonly PayableAccountNoteService $service
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'period' => ['required', 'string'],
        ]);

        $notes = $this->service->list($request->string('period')->toString());

        return PayableAccountNoteResource::collection($notes);
    }

    public function store(StorePayableAccountNoteRequest $request): JsonResponse
    {
        $note = $this->service->create($request->validated());

        return (new PayableAccountNoteResource($note))->response()->setStatusCode(201);
    }

    public function update(UpdatePayableAccountNoteRequest $request, PayableAccountNote $note): JsonResponse
    {
        $note = $this->service->update($note, $request->validated());

        return (new PayableAccountNoteResource($note))->response();
    }

    public function destroy(PayableAccountNote $note): JsonResponse
    {
        $this->service->delete($note);

        return response()->json(null, 204);
    }
}
