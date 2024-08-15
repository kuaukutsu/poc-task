<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\dto;

use kuaukutsu\poc\task\EntityArrable;
use kuaukutsu\poc\task\EntityFinally;

/**
 * @readonly
 */
final readonly class TaskOptions implements EntityArrable
{
    /**
     * @param float $timeout Seconds.
     * @param class-string<EntityFinally>|null $finally
     * @param array<string, scalar> $params
     */
    public function __construct(
        public float $timeout,
        public ?string $finally = null,
        public array $params = [],
    ) {
    }

    public function toArray(): array
    {
        return [
            'timeout' => $this->timeout,
            'finally' => $this->finally,
            'params' => $this->params,
        ];
    }
}
