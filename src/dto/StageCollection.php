<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\dto;

use kuaukutsu\ds\collection\Collection;

/**
 * @extends Collection<StageModel>
 */
final class StageCollection extends Collection
{
    public function getType(): string
    {
        return StageModel::class;
    }
}
