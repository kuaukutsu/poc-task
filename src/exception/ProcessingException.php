<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\exception;

use Throwable;
use RuntimeException;

final class ProcessingException extends RuntimeException
{
    public function __construct(string $message, int $code = 0, Throwable $exception = null)
    {
        parent::__construct($message, $code, $exception);
    }
}
