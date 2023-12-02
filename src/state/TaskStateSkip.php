<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\state;

use kuaukutsu\poc\task\TaskResponseInterface;

final class TaskStateSkip implements TaskStateInterface
{
    use TaskStateSerialize;

    public function __construct(private readonly TaskStateMessage $message)
    {
    }

    public function getFlag(): TaskFlag
    {
        return (new TaskFlag())->setSkiped();
    }

    public function getMessage(): TaskStateMessage
    {
        return $this->message;
    }

    public function getResponse(): ?TaskResponseInterface
    {
        return null;
    }
}
