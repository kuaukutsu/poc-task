<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests\stub;

use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\state\TaskStateMessage;
use kuaukutsu\poc\task\TaskHandlerBase;
use kuaukutsu\poc\task\TaskStageContext;

final class TestStageStub extends TaskHandlerBase
{
    public function __construct(
        public readonly string $name = 'Test.',
    ) {
    }

    public function handle(TaskStageContext $context): TaskStateInterface
    {
        return $this->success(
            new TaskStateMessage($this->name, 'Test example'),
            new TestResponse($this->name, date('c'))
        );
    }
}
