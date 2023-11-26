<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests\stub;

use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\state\TaskStateMessage;
use kuaukutsu\poc\task\TaskHandlerBase;
use kuaukutsu\poc\task\TaskStageContext;

final class TestWrapperStageStub extends TaskHandlerBase
{
    public function __construct(
        public readonly TestWrapperDto $dto,
        public readonly TestWrapperInterface $wrapper,
    ) {
    }

    public function handle(TaskStageContext $context): TaskStateInterface
    {
        return $this->success(
            new TaskStateMessage($this->dto->name, 'Test example'),
            $context,
            new TestResponse($this->wrapper->getName(), date('c'))
        );
    }
}
