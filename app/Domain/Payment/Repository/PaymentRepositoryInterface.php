<?php

namespace App\Domain\Payment\Repository;

use App\Domain\Shared\Repository\CreateInterface;
use App\Infrastructure\Models\Payment;

interface PaymentRepositoryInterface extends CreateInterface
{
    public function getByEventId(string $eventId): ?Payment;
}