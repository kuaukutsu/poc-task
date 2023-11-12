<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\state;

use kuaukutsu\poc\task\TaskResponseInterface;

/**
 * @psalm-immutable
 */
final class TaskStateReady implements TaskStateInterface
{
    use TaskStateSerialize;

    public function getFlag(): int
    {
        return (new TaskFlag())->toFlag();
    }

    public function getMessage(): TaskStateMessage
    {
        return new TaskStateMessage('Ready');
    }

    public function getResponse(): ?TaskResponseInterface
    {
        return null;
    }
}
