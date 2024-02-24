<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\handler;

use Throwable;
use kuaukutsu\poc\task\dto\StageModel;
use kuaukutsu\poc\task\exception\BuilderException;
use kuaukutsu\poc\task\state\TaskStateError;
use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\state\TaskStateMessage;
use kuaukutsu\poc\task\TaskStageContext;

final readonly class StageExecutor
{
    public function __construct(private StageHandlerFactory $handlerFactory)
    {
    }

    /**
     * @throws BuilderException
     */
    public function execute(StageModel $stage, TaskStageContext $context): TaskStateInterface
    {
        $handler = $this->handlerFactory->create($stage);

        try {
            return $handler->handle($context);
        } catch (Throwable $e) {
            return $handler->handleError(
                $context,
                new TaskStateError(
                    message: new TaskStateMessage($e->getMessage(), $e->getTraceAsString()),
                    flag: $stage->flag,
                )
            );
        }
    }
}
