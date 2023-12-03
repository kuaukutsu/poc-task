<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\dto;

use kuaukutsu\poc\task\EntityArrable;

/**
 * @readonly
 */
final class TaskOptions implements EntityArrable
{
    public function __construct(
        public float $timeout,
    ) {
    }

    public function toArray(): array
    {
        /**
         * @var array<string, scalar>
         */
        return get_object_vars($this);
    }
}
