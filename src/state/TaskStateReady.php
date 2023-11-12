<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\state;

use kuaukutsu\poc\task\TaskResponseInterface;

final class TaskStateReady implements TaskStateInterface
{
    use TaskStateSerialize;

    public function getFlag(): TaskFlag
    {
        return new TaskFlag();
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
