#!/usr/bin/env php
<?php

/**
 * Task Process Manager handler file.
 * @var array $definitions bootstrap.php
 */

declare(strict_types=1);

use DI\Container;
use kuaukutsu\poc\task\EntityWrapper;
use kuaukutsu\poc\task\service\TaskCreator;
use kuaukutsu\poc\task\TaskBuilder;
use kuaukutsu\poc\task\tests\nodes\EvenNode;
use kuaukutsu\poc\task\tests\nodes\OddNode;
use kuaukutsu\poc\task\tests\stub\IncreaseNumberStageStub;
use kuaukutsu\poc\task\tests\stub\NumberHandlerStageStub;
use kuaukutsu\poc\task\tests\stub\NumberSaveStageStub;
use kuaukutsu\poc\task\tools\NodeServiceFactory;

use function kuaukutsu\poc\task\tools\argument;

require __DIR__ . '/bootstrap.php';

$container = new Container($definitions);

/** @noinspection PhpUnhandledExceptionInspection */
$serviceEvenCreator = $container->get(NodeServiceFactory::class)
    ->factory($container->get(EvenNode::class), TaskCreator::class);

/** @noinspection PhpUnhandledExceptionInspection */
$serviceOddCreator = $container->get(NodeServiceFactory::class)
    ->factory($container->get(OddNode::class), TaskCreator::class);

/** @noinspection PhpUnhandledExceptionInspection */
$builderEven = $container->make(TaskBuilder::class, ['creator' => $serviceEvenCreator]);

/** @noinspection PhpUnhandledExceptionInspection */
$builderOdd = $container->make(TaskBuilder::class, ['creator' => $serviceOddCreator]);

$taskCount = (int)argument('task', 4);
while ($taskCount > 0) {
    $taskCount--;

    $builder = ($taskCount % 2) === 0 ? $builderEven : $builderOdd;

    $builder->build(
        $builder->create(
            $taskCount . ' date: ' . date('c'),
            new EntityWrapper(
                class: IncreaseNumberStageStub::class,
                params: [
                    'name' => 'Number initialization.',
                ],
            ),
            new EntityWrapper(
                class: NumberHandlerStageStub::class,
            ),
            new EntityWrapper(
                class: NumberSaveStageStub::class,
                params: [
                    'name' => 'All number save',
                ],
            ),
        )
    );
}
