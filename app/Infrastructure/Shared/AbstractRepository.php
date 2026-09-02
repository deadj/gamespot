<?php

namespace App\Infrastructure\Shared;

use App\Application\Shared\AbstractModelDTO;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractRepository
{
    protected Model $model;

    public function __construct()
    {
        $this->model = $this->getModel();
    }

    public function create(AbstractModelDTO $dto): Model
    {
        $model = $this->model->create($dto->toArray());
        return $model;
    }

    abstract protected function getModel(): Model;
}