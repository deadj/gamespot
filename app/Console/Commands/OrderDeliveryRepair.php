<?php

namespace App\Console\Commands;

use App\Application\Order\Service\OrderDeliveryRepairService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:order-delivery-repair')]
#[Description('Command for Order delivery repair')]
class OrderDeliveryRepair extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(OrderDeliveryRepairService $deliverService)
    {
        $deliverService->handleNotDeliveredOrders();
    }
}
