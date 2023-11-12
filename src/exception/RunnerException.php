<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\exception;

use Throwable;
use RuntimeException;

final class RunnerException extends RuntimeException
{
    public function __construct(string $message, Throwable $exception)
    {
        parent::__construct($message . PHP_EOL . $exception->getMessage(), 0, $exception);
    }
}
