<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests;

use kuaukutsu\poc\task\EntityTask;
use kuaukutsu\poc\task\EntityWrapper;
use kuaukutsu\poc\task\TaskBuilder;
use kuaukutsu\poc\task\tests\stub\TestStageStub;

trait TaskFaker
{
    private function generateTask(TaskBuilder $builder): EntityTask
    {
        return $builder->build(
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
