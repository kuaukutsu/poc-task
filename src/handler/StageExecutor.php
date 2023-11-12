<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\handler;

use DI\DependencyException;
use DI\NotFoundException;
use kuaukutsu\poc\task\dto\StageDto;
use kuaukutsu\poc\task\state\TaskStateError;
use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\state\TaskStateMessage;

final class StageExecutor
{
    public function __construct(
        private readonly StageHandlerFactory $handlerFactory,
        private readonly StageContextFactory $contextFactory,
    ) {
    }

    public function execute(StageDto $stage): TaskStateInterface
    {
        try {
            return $this->handlerFactory
                ->create($stage)
                ->handle(
                    $this->contextFactory->create($stage)
                );
        } catch (DependencyException | NotFoundException $e) {
            return new TaskStateError(
                uuid: $stage->taskUuid,
                message: new TaskStateMessage($e->getMessage()),
                flag: $stage->flag,
            );
        }
    }
}
