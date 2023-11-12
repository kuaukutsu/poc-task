<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\exception;

use LogicException;

final class NotFoundException extends LogicException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
