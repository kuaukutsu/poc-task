<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests\stub;

use kuaukutsu\poc\task\EntityFinally;
use kuaukutsu\poc\task\state\TaskStateInterface;
use RuntimeException;

final class TestExceptionFinally implements EntityFinally
{
    public function handle(string $uuid, TaskStateInterface $state): void
    {
        throw new RuntimeException("[$uuid] failure.");
    }
}
