<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\handler;

use Throwable;
use kuaukutsu\poc\task\dto\TaskModel;
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
    public function create(TaskModel $dto): EntityTask
    {
        try {
            return new Task(
                uuid: $dto->uuid,
                title: $dto->title,
                state: $this->prepareState($dto->state),
                options: $dto->options,
            );
        } catch (Throwable $exception) {
            throw new BuilderException("[$dto->uuid] TaskFactory failure.", $exception);
        }
    }
}
