<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\state\response;

use kuaukutsu\poc\task\TaskResponseInterface;

final class ResponseWrapper
{
    /**
     * @param class-string<TaskResponseInterface> $class
     * @param string $serializeData
     */
    public function __construct(
        public readonly string $class,
        public readonly string $serializeData,
    ) {
    }
}
