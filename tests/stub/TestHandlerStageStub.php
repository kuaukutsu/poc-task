<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests\stub;

use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\EntityWrapper;
use kuaukutsu\poc\task\TaskBuilder;
use kuaukutsu\poc\task\TaskHandlerBase;
use kuaukutsu\poc\task\TaskStageContext;

final class TestHandlerStageStub extends TaskHandlerBase
{
    public function __construct(private readonly TaskBuilder $builder)
    {
    }

    public function handle(TaskStageContext $context): TaskStateInterface
    {
        $task = $this->builder->create('Nested Task: ' . $context->task);
        $task->addStage(
            new EntityWrapper(
                class: TestStageStub::class,
                params: [
                    'name' => 'Nested one: ' . $context->task,
                ],
            ),
            new EntityWrapper(
                class: TestStageStub::class,
                params: [
                    'name' => 'Nested two: ' . $context->task,
                ],
            ),
        );

        return $this->wait(
            $this->builder->build($task, $context),
            $context,
        );
    }
}
