<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests\stub;

use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\EntityWrapper;
use kuaukutsu\poc\task\TaskBuilder;
use kuaukutsu\poc\task\TaskHandlerBase;
use kuaukutsu\poc\task\TaskStageContext;
use kuaukutsu\poc\task\tests\service\StubNode;

final class TestHandlerStageStub extends TaskHandlerBase
{
    public function __construct(
        private readonly TaskBuilder $builder,
        private readonly StubNode $node,
    ) {
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
            $this->builder->build($this->node, $task, $context),
            $context,
        );
    }
}
