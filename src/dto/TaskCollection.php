<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\dto;

use kuaukutsu\ds\collection\Collection;

/**
 * @extends Collection<TaskModel>
 */
final class TaskCollection extends Collection
{
    public function getType(): string
    {
        return TaskModel::class;
    }
}
