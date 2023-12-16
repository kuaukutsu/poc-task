<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\dto;

use kuaukutsu\poc\task\EntityArrable;
use kuaukutsu\poc\task\EntityFinally;

/**
 * @readonly
 */
final class TaskOptions implements EntityArrable
{
    /**
     * @param float $timeout В секундах.
     * @param class-string<EntityFinally>|null $finally
     */
    public function __construct(
        public readonly float $timeout,
        public readonly ?string $finally = null,
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
