<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\state;

use Serializable;

final readonly class TaskStateMessage implements Serializable
{
    use TaskStateSerialize;

    public function __construct(
        public string $message,
        public ?string $description = null,
    ) {
    }
}
