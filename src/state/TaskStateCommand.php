<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\state;

use kuaukutsu\poc\task\EntityTask;
use kuaukutsu\poc\task\exception\NotFoundException;
use kuaukutsu\poc\task\TaskResponseInterface;
use kuaukutsu\poc\task\TaskStageContext;

trait TaskStateCommand
{
    final protected function success(
        TaskStateMessage $message,
        ?TaskResponseInterface $response = null,
    ): TaskStateSuccess {
        return new TaskStateSuccess(
            message: $message,
            response: $response,
        );
    }

    final protected function error(
        TaskStateMessage $message,
        ?TaskResponseInterface $response = null,
    ): TaskStateError {
        return new TaskStateError(
            message: $message,
            response: $response,
        );
    }

    final protected function pause(string $reason): TaskStatePaused
    {
        return new TaskStatePaused(
            message: new TaskStateMessage($reason),
        );
    }

    final protected function skip(string $reason): TaskStateSkip
    {
        return new TaskStateSkip(
            message: new TaskStateMessage($reason),
        );
    }

    final protected function cancel(string $reason): TaskStateCanceled
    {
        return new TaskStateCanceled(
            message: new TaskStateMessage($reason),
        );
    }

    final protected function wait(EntityTask $task, TaskStageContext $context): TaskStateWaiting
    {
        return new TaskStateWaiting(
            uuid: $context->stage,
            task: $task->getUuid(),
            message: new TaskStateMessage(
                "Waiting [{$task->getUuid()}] {$task->getTitle()}."
            ),
        );
    }

    /**
     * @throws NotFoundException
     */
    final protected function preparePrevious(TaskStageContext $context): TaskStateInterface
    {
        if ($context->previous === null) {
            throw new NotFoundException("[$context->stage] previous not found.");
        }

        return $context->previous;
    }
}
