<?php

namespace Modules\TransportCard\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Modules\TransportCard\Http\Requests\StoreTransportCardRequest;
use Modules\TransportCard\Http\Requests\UpdateTransportCardRequest;
use Modules\TransportCard\Http\Resources\TransportCardResource;
use Modules\TransportCard\Models\TransportCard;
use Modules\TransportCard\Services\TransportCardService;

class TransportCardController extends Controller
{
    public function __construct(
        private readonly TransportCardService $service
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $cards = TransportCard::query()->orderBy('name')->get();

        return TransportCardResource::collection($cards);
    }

    public function store(StoreTransportCardRequest $request): JsonResponse
    {
        $transportCard = TransportCard::query()->create($request->validated());

        return (new TransportCardResource($transportCard))->response()->setStatusCode(201);
    }

    public function show(TransportCard $transport_card): TransportCardResource
    {
        return new TransportCardResource($transport_card);
    }

    public function update(UpdateTransportCardRequest $request, TransportCard $transport_card): TransportCardResource
    {
        $transport_card->update($request->validated());

        return new TransportCardResource($transport_card->fresh());
    }

    public function destroy(TransportCard $transport_card): JsonResponse
    {
        $transport_card->delete();

        return response()->json(null, 204);
    }

    public function balance(Request $request, TransportCard $transport_card): JsonResponse
    {
        $forceRefresh = $request->boolean('refresh');

        try {
            $data = $this->service->getBalance($transport_card, $forceRefresh);

            return response()->json($data);
        } catch (\RuntimeException $e) {
            return response()->json(
                ['message' => 'Unable to fetch transport card balance. Please try again later.'],
                Response::HTTP_BAD_GATEWAY
            );
        }
    }

    public function refresh(TransportCard $transport_card): JsonResponse
    {
        try {
            $data = $this->service->getBalance($transport_card, true);

            return response()->json($data);
        } catch (\RuntimeException $e) {
            return response()->json(
                ['message' => 'Unable to fetch transport card balance. Please try again later.'],
                Response::HTTP_BAD_GATEWAY
            );
        }
    }
}
