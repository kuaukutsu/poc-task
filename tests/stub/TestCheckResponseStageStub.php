<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests\stub;

use kuaukutsu\poc\task\state\TaskStateError;
use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\state\TaskStateMessage;
use kuaukutsu\poc\task\TaskHandlerBase;
use kuaukutsu\poc\task\TaskStageContext;

final class TestCheckResponseStageStub extends TaskHandlerBase
{
    public function __construct(
        public readonly string $name = 'Test.',
    ) {
    }

    public function handle(TaskStageContext $context): TaskStateInterface
    {
        $previous = $this->preparePrevious($context);

        return $this->success(
            $previous->getMessage(),
            new TestResponse($this->name, date('c'))
        );
    }

    public function handleError(TaskStageContext $context, TaskStateError $state): TaskStateError
    {
        return new TaskStateError(
            new TaskStateMessage(
                $state->getMessage()->message,
                $context->task,
            ),
        );
    }
}
