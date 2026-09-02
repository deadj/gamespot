<?php

namespace App\Application\Payment\DTO;

use App\Application\Shared\AbstractModelDTO;
use App\Domain\Payment\Enum\PaymentStatus;
use Carbon\Carbon;

class PaymentDTO extends AbstractModelDTO
{
    public function __construct(
        public string $eventId,
        public string $orderPublicId,
        public PaymentStatus $status,
        public float $amount,
        public string $currency,
        public Carbon $eventCreatedAt,
    ) {}
}