<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests\stub;

use kuaukutsu\poc\task\TaskResponseInterface;

final class NumberResponse implements TaskResponseInterface
{
    public function __construct(
        public readonly int $number,
        public readonly string $date,
    ) {
    }
}
