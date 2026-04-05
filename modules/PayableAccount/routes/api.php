<?php

use Illuminate\Support\Facades\Route;
use Modules\PayableAccount\Http\Controllers\PayableAccountController;
use Modules\PayableAccount\Http\Controllers\PayableAccountNoteController;
use Modules\PayableAccount\Http\Controllers\PayableAccountPaymentController;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('payable-accounts/counts', [PayableAccountController::class, 'counts'])
        ->name('payable-accounts.counts');
    Route::apiResource('payable-accounts', PayableAccountController::class);
    Route::post('payable-accounts/{payable_account}/payments', [PayableAccountPaymentController::class, 'store'])
        ->name('payable-accounts.payments.store');
    Route::put('payable-accounts/{payable_account}/payments/{payment}', [PayableAccountPaymentController::class, 'update'])
        ->name('payable-accounts.payments.update');
    Route::apiResource('payable-account-notes', PayableAccountNoteController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['payable-account-notes' => 'note']);
});
