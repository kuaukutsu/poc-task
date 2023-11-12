<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\exception;

use Exception;

final class UnsupportedException extends Exception
{
    public function __construct(string $message = 'Unsuported operation.')
    {
        parent::__construct($message);
    }
}
