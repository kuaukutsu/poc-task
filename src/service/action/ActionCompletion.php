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
use kuaukutsu\poc\task\state\TaskStatePaused;
use kuaukutsu\poc\task\state\TaskStateSuccess;
use kuaukutsu\poc\task\handler\StateFactory;
use kuaukutsu\poc\task\handler\TaskFactory;
use kuaukutsu\poc\task\service\StageCommand;
use kuaukutsu\poc\task\service\StageQuery;
use kuaukutsu\poc\task\service\TaskCommand;
use kuaukutsu\poc\task\EntityTask;
use kuaukutsu\poc\task\EntityUuid;

final class ActionCompletion implements TaskAction
{
    public function __construct(
        private readonly StageQuery $stageQuery,
        private readonly StageCommand $stageCommand,
        private readonly StateFactory $stateFactory,
        private readonly TaskCommand $taskCommand,
        private readonly TaskFactory $factory,
        private readonly ActionCancel $actionCancel,
    ) {
    }

    public function execute(EntityTask $task, ?TaskStateInterface $state = null): EntityTask
    {
        if ($task->getState()->getFlag()->isFinished()) {
            return $task;
        }

        $state ??= $this->handleStagesState($task);
        if ($state === null) {
            return $this->actionCancel->execute($task);
        }

        return $this->factory->create(
            $this->taskCommand->state(
                new EntityUuid($task->getUuid()),
                new TaskModelState($state),
            )
        );
    }

    private function handleStagesState(EntityTask $task): ?TaskStateInterface
    {
        $uuid = new EntityUuid($task->getUuid());
        $context = new ResponseContextWrapper();
        foreach ($this->stageQuery->iterableByTask($uuid) as $item) {
            $state = $this->stateCreate($item);
            if ($state->getFlag()->isError()) {
                return new TaskStateError(
                    message: $state->getMessage(),
                    flag: $task->getFlag(),
                );
            }

            if ($state->getFlag()->isFinished()) {
                $response = $state->getResponse();
                if ($response !== null) {
                    $state->getFlag()->isSuccess()
                        ? $context->pushSuccessResponse($response)
                        : $context->pushFailureResponse($response);
                }

                continue;
            }

            if ($state->getFlag()->isPaused()) {
                return new TaskStatePaused(
                    message: $state->getMessage(),
                    flag: $task->getFlag(),
                );
            }

            if ($state->getFlag()->isReady() || $state->getFlag()->isPromised()) {
                return $state;
            }

            return null;
        }

        $this->stageCommand->removeByTask($uuid);

        return new TaskStateSuccess(
            message: new TaskStateMessage('TaskCompletion'),
            response: $context,
        );
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
}
