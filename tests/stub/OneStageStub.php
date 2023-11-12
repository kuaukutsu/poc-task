<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests\stub;

use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\state\TaskStateMessage;
use kuaukutsu\poc\task\TaskStageBase;
use kuaukutsu\poc\task\TaskStageContext;

final class OneStageStub extends TaskStageBase
{
    public function __construct(
        public readonly string $name,
        private readonly string $description = 'test',
    ) {
    }

    public function handle(TaskStageContext $context): TaskStateInterface
    {
        return $this->success(
            new TaskStateMessage($this->name . ' test', $this->description),
            $context
        );
    }
}
