<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\processing;

use kuaukutsu\poc\task\exception\ProcessingException;
use kuaukutsu\poc\task\handler\StateFactory;
use kuaukutsu\poc\task\service\action\ActionSuccess;
use kuaukutsu\poc\task\service\StageQuery;
use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\state\TaskStateMessage;
use kuaukutsu\poc\task\state\TaskStateSuccess;
use kuaukutsu\poc\task\EntityUuid;
use kuaukutsu\poc\task\EntityTask;

final class TaskProcessCompletion
{
    public function __construct(
        private readonly StageQuery $query,
        private readonly StateFactory $stateFactory,
        private readonly ActionSuccess $actionSuccess,
    ) {
    }

    /**
     * @throws ProcessingException
     */
    public function success(EntityTask $task): TaskStateInterface
    {
        if ($task->getState()->getFlag()->isFinished()) {
            return $task->getState();
        }

        // получить все этапы, собрать все не пустые response
        $collection = [];
        foreach ($this->query->findByTask(new EntityUuid($task->getUuid())) as $item) {
            $state = $this->stateFactory->create($item->state);
            if ($state->getFlag()->isFinished() === false) {
                throw new ProcessingException(
                    "[$item->taskUuid] Task cannot be completed. There are unfinished [$item->state] stages."
                );
            }

            $response = $state->getResponse();
            if ($response !== null) {
                $collection[] = $response;
            }
        }

        // записать в state
        $state = new TaskStateSuccess(
            uuid: $task->getUuid(),
            message: new TaskStateMessage('TaskProcessCompletion'),
            response: new TaskResponseContext($collection),
        );

        return $this->actionSuccess
            ->execute($task, $state)
            ->getState();
    }
}
