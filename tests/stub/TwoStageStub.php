<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests\stub;

use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\state\TaskStateMessage;
use kuaukutsu\poc\task\TaskStageBase;
use kuaukutsu\poc\task\TaskStageContext;

final class TwoStageStub extends TaskStageBase
{
    public function __construct(public readonly string $name)
    {
    }

    public function handle(TaskStageContext $context): TaskStateInterface
    {
        if ($context->previous === null) {
            return $this->success(
                new TaskStateMessage($this->name . ' success'),
                $context,
            );
        }

        if ($context->previous->getFlag()->isError()) {
            return $this->error(
                $context->previous->getMessage(),
                $context,
            );
        }

        return $this->success(
            new TaskStateMessage(
                $this->name . ' success',
                $context->previous->getMessage()->message,
            ),
            $context,
        );
    }
}
