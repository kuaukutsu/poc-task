<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\processing;

use kuaukutsu\poc\task\TaskResponseInterface;

final class TaskResponseContext implements \kuaukutsu\poc\task\TaskResponseInterface
{
    /**
     * @param TaskResponseInterface[] $items
     */
    public function __construct(
        public readonly array $items,
    ) {
    }
}
