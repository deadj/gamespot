<?php

namespace App\Infrastructure\Shared\Trait;

trait MorphClassTrait
{
    public static function morphClass(): string
    {
        return (new static())->getMorphClass();
    }    
}