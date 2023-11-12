<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests\stub;

use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\TaskBuilder;
use kuaukutsu\poc\task\EntityWrapper;
use kuaukutsu\poc\task\TaskStageBase;
use kuaukutsu\poc\task\TaskStageContext;

final class PromiseStageStub extends TaskStageBase
{
    public function __construct(
        public readonly string $name,
        private readonly TaskBuilder $builder,
    ) {
    }

    public function handle(TaskStageContext $context): TaskStateInterface
    {
        $task = $this->builder->create('Nested Task');
        $task->addStage(
            new EntityWrapper(
                class: OneStageStub::class,
                params: [
                    'name' => 'Nested one',
                ],
            ),
            new EntityWrapper(
                class: TwoStageStub::class,
                params: [
                    'name' => 'Nested two',
                ],
            ),
        );

        return $this->wait(
            $this->builder->build($task, $context),
            $context,
        );
    }
}
