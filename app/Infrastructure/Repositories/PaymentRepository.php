<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Payment\Repository\PaymentRepositoryInterface;
use App\Infrastructure\Models\Payment;
use App\Infrastructure\Shared\AbstractRepository;
use Illuminate\Database\Eloquent\Model;
use Override;

class PaymentRepository extends AbstractRepository implements PaymentRepositoryInterface
{
    #[Override]
    public function getByEventId(string $eventId): ?Payment
    {
        return $this->model->where('event_id', $eventId)->first();    
    }

    #[Override]
    protected function getModel(): Model
    {
        return new Payment();
    }
}