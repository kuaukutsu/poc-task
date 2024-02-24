<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\state\response;

use kuaukutsu\poc\task\TaskResponseInterface;

final readonly class ResponseWrapper
{
    /**
     * @param class-string<TaskResponseInterface> $class
     */
    public function __construct(
        public string $class,
        public string $serializeData,
    ) {
    }
}
