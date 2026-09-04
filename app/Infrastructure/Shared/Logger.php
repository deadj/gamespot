<?php

namespace App\Infrastructure\Shared;

use App\Domain\Shared\LoggerInterface;
use Illuminate\Support\Facades\Log;
use Override;

class Logger implements LoggerInterface
{
    #[Override]
    public function info(string $message, array $context = []): void
    {
        Log::info($message, $context);
    }
 
    #[Override]
    public function warning(string $message, array $context = []): void
    {
        Log::warning($message, $context);
    }
 
    #[Override]
    public function error(string $message, array $context = []): void
    {
        Log::error($message, $context);
    }
}