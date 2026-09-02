<?php

namespace App\Infrastructure\Repositories;

use App\Application\Shared\AbstractModelDTO;
use App\Domain\Payment\Repository\PaymentLogRepositoryInterface;
use App\Infrastructure\Models\PaymentLog;
use App\Infrastructure\Shared\AbstractRepository;
use Illuminate\Database\Eloquent\Model;
use Override;

class PaymentLogRepository extends AbstractRepository implements PaymentLogRepositoryInterface
{
    #[Override]
    public function create(AbstractModelDTO $dto): Model
    {
        $log = $this->model->create([
            'event_id' => $dto->eventId,
            'data' => $dto->toArray(),
        ]);
        return $log;
    }

    #[Override]
    protected function getModel(): Model
    {
        return new PaymentLog();
    }
}