<?php

namespace App\Application\Shared;

use BackedEnum;
use Illuminate\Support\Str;

class AbstractModelDTO
{
    public function toArray(): array
    {
        $arrayWithCamelCase = get_object_vars($this);
        $arrayWithSnakeCase = [];

        foreach ($arrayWithCamelCase as $key => $value) {
            if ($value === null)
                continue;
            
            $arrayWithSnakeCase[Str::snake($key)] = $value instanceof BackedEnum ? $value->value : $value;
        } 

        return $arrayWithSnakeCase;
    }    
}