#!/usr/bin/env php
<?php

/**
 * Task Process Manager handler file.
 * @var array $definitions bootstrap.php
 */

declare(strict_types=1);

use DI\Container;
use kuaukutsu\poc\task\TaskBuilder;
use kuaukutsu\poc\task\EntityWrapper;
use kuaukutsu\poc\task\tests\stub\OneStageStub;
use kuaukutsu\poc\task\tests\stub\PromiseStageStub;
use kuaukutsu\poc\task\tests\stub\TwoStageStub;

use function kuaukutsu\poc\task\tools\argument;

require __DIR__ . '/bootstrap.php';

$container = new Container($definitions);

/** @noinspection PhpUnhandledExceptionInspection */
$builder = $container->get(TaskBuilder::class);

$taskCount = (int)argument('task', 4);
while ($taskCount > 0) {
    $taskCount--;

    $builder->build(
        $builder->create(
            'title',
            new EntityWrapper(
                class: OneStageStub::class,
                params: [
                    'name' => 'one',
                ],
            ),
            new EntityWrapper(
                class: PromiseStageStub::class,
                params: [
                    'name' => 'promise',
                ],
            ),
            new EntityWrapper(
                class: TwoStageStub::class,
                params: [
                    'name' => 'two',
                ],
            ),
        )
    );
}
