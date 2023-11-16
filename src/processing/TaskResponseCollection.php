<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\processing;

use kuaukutsu\ds\collection\Collection;
use kuaukutsu\poc\task\TaskResponseInterface;

/**
 * @extends Collection<TaskResponseInterface>
 */
final class TaskResponseCollection extends Collection implements TaskResponseInterface
{
    public function getType(): string
    {
        return TaskResponseInterface::class;
    }
}
