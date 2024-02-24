<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\state;

use kuaukutsu\poc\task\TaskResponseInterface;

final readonly class TaskStateSkip implements TaskStateInterface
{
    use TaskStateSerialize;

    public function __construct(private TaskStateMessage $message)
    {
    }

    public function getFlag(): TaskFlag
    {
        return (new TaskFlag())->setSkipped();
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
