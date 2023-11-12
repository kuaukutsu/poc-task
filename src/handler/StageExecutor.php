<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\handler;

use DI\DependencyException;
use DI\NotFoundException;
use kuaukutsu\poc\task\dto\StageDto;
use kuaukutsu\poc\task\state\TaskStateError;
use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\state\TaskStateMessage;
use kuaukutsu\poc\task\TaskStageContext;

final class StageExecutor
{
    public function __construct(
        private readonly StageHandlerFactory $handlerFactory,
    ) {
    }

    public function execute(StageDto $stage, TaskStageContext $context): TaskStateInterface
    {
        try {
            return $this->handlerFactory
                ->create($stage)
                ->handle($context);
        } catch (DependencyException | NotFoundException $e) {
            return new TaskStateError(
                uuid: $stage->taskUuid,
                message: new TaskStateMessage($e->getMessage()),
                flag: $stage->flag,
            );
        }
    }
}
