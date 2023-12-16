<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests;

use kuaukutsu\poc\task\EntityNode;
use kuaukutsu\poc\task\EntityTask;
use kuaukutsu\poc\task\EntityWrapper;
use kuaukutsu\poc\task\TaskBuilder;
use kuaukutsu\poc\task\tests\stub\TestStageStub;

trait TaskFaker
{
    private function generateTask(EntityNode $node, TaskBuilder $builder): EntityTask
    {
        return $builder->build(
            $node,
            $builder->create(
                'task test builder',
                new EntityWrapper(
                    class: TestStageStub::class,
                    params: [
                        'name' => 'Test initialization.',
                    ],
                ),
            )
        );
    }
}
