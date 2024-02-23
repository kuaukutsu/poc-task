<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\service\action;

use kuaukutsu\poc\task\dto\StageModel;
use kuaukutsu\poc\task\dto\TaskModelState;
use kuaukutsu\poc\task\exception\ProcessingException;
use kuaukutsu\poc\task\state\response\ResponseContextWrapper;
use kuaukutsu\poc\task\state\TaskStateError;
use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\state\TaskStateMessage;
use kuaukutsu\poc\task\state\TaskStateSuccess;
use kuaukutsu\poc\task\handler\StateFactory;
use kuaukutsu\poc\task\handler\TaskFactory;
use kuaukutsu\poc\task\service\StageCommand;
use kuaukutsu\poc\task\service\StageQuery;
use kuaukutsu\poc\task\service\TaskCommand;
use kuaukutsu\poc\task\EntityTask;
use kuaukutsu\poc\task\EntityUuid;
use kuaukutsu\poc\task\TaskResponseInterface;

final class ActionCompletion implements TaskAction
{
    public function __construct(
        private readonly StageQuery $stageQuery,
        private readonly StageCommand $stageCommand,
        private readonly StateFactory $stateFactory,
        private readonly TaskCommand $taskCommand,
        private readonly TaskFactory $factory,
    ) {
    }

    public function execute(EntityTask $task, ?TaskStateInterface $state = null): EntityTask
    {
        if ($task->isFinished()) {
            return $task;
        }

        return $this->factory->create(
            $this->taskCommand->state(
                new EntityUuid($task->getUuid()),
                new TaskModelState(
                    $state ?? $this->handleStagesState($task)
                ),
            )
        );
    }

    private function handleStagesState(EntityTask $task): TaskStateInterface
    {
        $uuid = new EntityUuid($task->getUuid());
        $state = $task->isPromised()
            ? $this->handleContextByPromised($uuid)
            : $this->handleContext($uuid);

        if ($state->getFlag()->isError() === false) {
            $this->stageCommand->removeByTask($uuid);
        }

        return $state;
    }

    private function stateCreate(StageModel $stage): TaskStateInterface
    {
        try {
            return $this->stateFactory->create($stage->taskUuid, $stage->state);
        } catch (ProcessingException $exception) {
            return new TaskStateError(
                new TaskStateMessage(
                    $exception->getMessage(),
                    $exception->getTraceAsString(),
                ),
            );
        }
    }

    private function handleContext(EntityUuid $uuid): TaskStateInterface
    {
        $context = new ResponseContextWrapper();
        foreach ($this->stageQuery->iterableByTask($uuid) as $item) {
            $state = $this->stateCreate($item);
            if ($state->getFlag()->isError()) {
                return new TaskStateError(
                    message: $state->getMessage(),
                );
            }

            if ($state->getFlag()->isFinished()) {
                $response = $state->getResponse();
                if ($response instanceof TaskResponseInterface) {
                    $state->getFlag()->isSuccess()
                        ? $context->pushSuccessResponse($response)
                        : $context->pushFailureResponse($response);
                }
            }
        }

        return new TaskStateSuccess(
            message: new TaskStateMessage('TaskCompletion'),
            response: $context,
        );
    }

    private function handleContextByPromised(EntityUuid $uuid): TaskStateInterface
    {
        $context = new ResponseContextWrapper();
        foreach ($this->stageQuery->iterableByTask($uuid) as $item) {
            $state = $this->stateCreate($item);
            if ($state->getFlag()->isFinished()) {
                $response = $state->getResponse();
                if ($response instanceof TaskResponseInterface) {
                    $state->getFlag()->isSuccess()
                        ? $context->pushSuccessResponse($response)
                        : $context->pushFailureResponse($response);
                }
            }
        }

        return new TaskStateSuccess(
            message: new TaskStateMessage('TaskPromiseCompletion'),
            response: $context,
        );
    }
}
