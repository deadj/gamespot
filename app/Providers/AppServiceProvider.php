<?php

namespace App\Providers;

use App\Domain\Order\Repository\KeyRepositoryInterface;
use App\Domain\Order\Repository\OrderRepositoryInterface;
use App\Domain\Payment\Repository\PaymentLogRepositoryInterface;
use App\Domain\Payment\Repository\PaymentRepositoryInterface;
use App\Domain\Product\Repository\ProductRepositoryInterface;
use App\Domain\Supplier\Interface\SupplierInterface;
use App\Infrastructure\Repositories\KeyRepository;
use App\Infrastructure\Repositories\OrderRepository;
use App\Infrastructure\Repositories\PaymentLogRepository;
use App\Infrastructure\Repositories\PaymentRepository;
use App\Infrastructure\Repositories\ProductRepository;
use App\Infrastructure\Supplier\SupplierClient;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(KeyRepositoryInterface::class, KeyRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(PaymentRepositoryInterface::class, PaymentRepository::class);
        $this->app->bind(PaymentLogRepositoryInterface::class, PaymentLogRepository::class);
        $this->app->bind(SupplierInterface::class, SupplierClient::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
