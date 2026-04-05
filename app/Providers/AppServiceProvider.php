<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\PayableAccount\Contracts\Repositories\PayableAccountPaymentRepositoryInterface;
use Modules\PayableAccount\Contracts\Repositories\PayableAccountRepositoryInterface;
use Modules\PayableAccount\Models\PayableAccountPayment;
use Modules\PayableAccount\Repositories\PayableAccountPaymentRepository;
use Modules\PayableAccount\Repositories\PayableAccountRepository;
use Modules\TransportCard\Contracts\Repositories\TransportCardBalanceRepositoryInterface;
use Modules\TransportCard\Repositories\TransportCardBalanceRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            PayableAccountRepositoryInterface::class,
            PayableAccountRepository::class
        );
        $this->app->bind(
            PayableAccountPaymentRepositoryInterface::class,
            PayableAccountPaymentRepository::class
        );
        $this->app->bind(
            TransportCardBalanceRepositoryInterface::class,
            TransportCardBalanceRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Route::bind('payment', fn (string $value) => PayableAccountPayment::findOrFail($value));
    }
}
