<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\handler;

use Throwable;
use DI\DependencyException;
use DI\NotFoundException;
use kuaukutsu\poc\task\dto\StageDto;
use kuaukutsu\poc\task\exception\BuilderException;
use kuaukutsu\poc\task\state\TaskStateError;
use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\state\TaskStateMessage;
use kuaukutsu\poc\task\TaskStageContext;
use kuaukutsu\poc\task\EntityStage;

final class StageExecutor
{
    public function __construct(
        private readonly StageHandlerFactory $handlerFactory,
    ) {
    }

    /**
     * @throws BuilderException
     */
    public function execute(StageDto $stage, TaskStageContext $context): TaskStateInterface
    {
        try {
            /** @psalm-var EntityStage $handler */
            $handler = $this->handlerFactory->create($stage);
        } catch (DependencyException | NotFoundException $exception) {
            throw new BuilderException(
                "[$stage->uuid] StageHandler error: " . $exception->getMessage(),
                $exception,
            );
        }

        try {
            return $handler->handle($context);
        } catch (Throwable $e) {
            return $handler->handleError(
                $context,
                new TaskStateError(
                    uuid: $stage->taskUuid,
                    message: new TaskStateMessage($e->getMessage(), $e->getTraceAsString()),
                    flag: $stage->flag,
                    response: $context->previous?->getResponse(),
                )
            );
        }
    }
}
