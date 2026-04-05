<?php

use Illuminate\Support\Facades\Route;
use Modules\TransportCard\Http\Controllers\TransportCardController;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('transport-cards/{transport_card}/balance', [TransportCardController::class, 'balance'])
        ->name('transport-cards.balance');
    Route::post('transport-cards/{transport_card}/refresh', [TransportCardController::class, 'refresh'])
        ->name('transport-cards.refresh');
    Route::apiResource('transport-cards', TransportCardController::class);
});
