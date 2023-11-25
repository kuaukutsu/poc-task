<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\handler;

use TypeError;
use kuaukutsu\poc\task\dto\TaskDto;
use kuaukutsu\poc\task\exception\BuilderException;
use kuaukutsu\poc\task\state\TaskStatePrepare;
use kuaukutsu\poc\task\EntityTask;
use kuaukutsu\poc\task\Task;

final class TaskFactory
{
    use TaskStatePrepare;

    /**
     * @throws BuilderException
     */
    public function create(TaskDto $dto): EntityTask
    {
        try {
            return new Task(
                uuid: $dto->uuid,
                title: $dto->title,
                state: $this->prepareState($dto->state),
            );
        } catch (TypeError $exception) {
            throw new BuilderException(
                "[$dto->uuid] TaskFactory error: " . $exception->getMessage(),
                $exception,
            );
        }
    }
}
