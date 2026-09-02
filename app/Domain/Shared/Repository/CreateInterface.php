<?php

namespace App\Domain\Shared\Repository;

use App\Application\Shared\AbstractModelDTO;
use Illuminate\Database\Eloquent\Model;

interface CreateInterface
{
    public function create(AbstractModelDTO $dto): Model;
}