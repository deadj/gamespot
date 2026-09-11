<?php

namespace App\Providers;

use App\Application\Order\Service\SupplierHandler;
use App\Domain\Money\Repository\MoneyMoveRepositoryInterface;
use App\Domain\Order\Repository\OrderItemRepositoryInterface;
use App\Domain\Supplier\Repository\KeyRepositoryInterface;
use App\Domain\Order\Repository\OrderRepositoryInterface;
use App\Domain\Payment\Repository\PaymentLogRepositoryInterface;
use App\Domain\Payment\Repository\PaymentRepositoryInterface;
use App\Domain\Product\Repository\ProductRepositoryInterface;
use App\Domain\Shared\LoggerInterface;
use App\Infrastructure\Repositories\KeyRepository;
use App\Infrastructure\Repositories\MoneyMoveRepository;
use App\Infrastructure\Repositories\OrderItemRepository;
use App\Infrastructure\Repositories\OrderRepository;
use App\Infrastructure\Repositories\PaymentLogRepository;
use App\Infrastructure\Repositories\PaymentRepository;
use App\Infrastructure\Repositories\ProductRepository;
use App\Infrastructure\Shared\Logger;
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
        $this->app->bind(OrderItemRepositoryInterface::class, OrderItemRepository::class);
        $this->app->bind(KeyRepositoryInterface::class, KeyRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(PaymentRepositoryInterface::class, PaymentRepository::class);
        $this->app->bind(PaymentLogRepositoryInterface::class, PaymentLogRepository::class);
        $this->app->bind(MoneyMoveRepositoryInterface::class, MoneyMoveRepository::class);

        $this->app->bind(LoggerInterface::class, Logger::class);

        $this->app->singleton(SupplierHandler::class, function ($app) {
            $keyRepository = $app->make(KeyRepositoryInterface::class);
            $orderItemsRepository = $app->make(OrderItemRepositoryInterface::class);
 
            return new SupplierHandler(
                supplierA: new SupplierClient(
                    name: 'A',
                    keyRepository: $keyRepository,
                    errorPercent: config('services.suppliers.a.error_percent'),
                    timeoutPercent: config('services.suppliers.a.timeout_percent'),
                ),
                supplierB: new SupplierClient(
                    name: 'B',
                    keyRepository: $keyRepository,
                    errorPercent: config('services.suppliers.b.error_percent'),
                    timeoutPercent: config('services.suppliers.b.timeout_percent'),
                ),
                logger: $app->make(LoggerInterface::class),
                orderItemRepository: $orderItemsRepository,
            );
        });        
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
