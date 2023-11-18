<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\service\action;

use kuaukutsu\poc\task\dto\TaskModel;
use kuaukutsu\poc\task\handler\StateFactory;
use kuaukutsu\poc\task\handler\TaskFactory;
use kuaukutsu\poc\task\service\StageCommand;
use kuaukutsu\poc\task\service\StageQuery;
use kuaukutsu\poc\task\service\TaskCommand;
use kuaukutsu\poc\task\state\TaskResponseContext;
use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\state\TaskStateMessage;
use kuaukutsu\poc\task\state\TaskStatePaused;
use kuaukutsu\poc\task\state\TaskStateSuccess;
use kuaukutsu\poc\task\EntityUuid;
use kuaukutsu\poc\task\EntityTask;

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

        $model = $this->taskCommand->update(
            new EntityUuid($task->getUuid()),
            TaskModel::hydrate(
                [
                    'flag' => $state->getFlag()->toValue(),
                    'state' => serialize($state),
                ]
            ),
        );

        return $this->factory->create($model);
    }

    private function handleStagesState(EntityTask $task): ?TaskStateInterface
    {
        $context = new TaskResponseContext();
        $uuid = new EntityUuid($task->getUuid());
        foreach ($this->stageQuery->findByTask($uuid) as $item) {
            $state = $this->stateFactory->create($item->state);
            if ($state->getFlag()->isFinished()) {
                $response = $state->getResponse();
                if ($response !== null) {
                    $state->getFlag()->isSuccess()
                        ? $context->pushSuccessResponse($item->uuid, $response)
                        : $context->pushFailureResponse($item->uuid, $response);
                }

                continue;
            }

            if ($state->getFlag()->isPaused()) {
                return new TaskStatePaused(
                    uuid: $task->getUuid(),
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
            uuid: $task->getUuid(),
            message: new TaskStateMessage('TaskCompletion'),
            response: $context,
        );
    }
}