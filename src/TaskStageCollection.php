<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task;

use kuaukutsu\ds\collection\Collection;

/**
 * @extends Collection<TaskStageInterface>
 */
final class TaskStageCollection extends Collection
{
    public function getType(): string
    {
        return TaskStageInterface::class;
    }

    public function getChecksum(): string
    {
        $string = '';
        foreach ($this as $item) {
            $string .= serialize($item);
        }

        return md5($string);
    }
}
