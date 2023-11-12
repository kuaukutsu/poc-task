<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests\stub;

use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\state\TaskStateMessage;
use kuaukutsu\poc\task\TaskStageBase;
use kuaukutsu\poc\task\TaskStageContext;

final class ThreeStageStub extends TaskStageBase
{
    public function __construct(public readonly string $name)
    {
    }

    public function handle(TaskStageContext $context): TaskStateInterface
    {
        return $this->error(
            new TaskStateMessage('error'),
            $context,
        );
    }
}
