<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\state;

use Serializable;

/**
 * @psalm-immutable
 */
final class TaskStateMessage implements Serializable
{
    use TaskStateSerialize;

    public function __construct(
        public readonly string $message,
        public readonly ?string $description = null,
    ) {
    }
}
