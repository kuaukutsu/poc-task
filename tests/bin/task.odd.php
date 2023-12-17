#!/usr/bin/env php
<?php

/**
 * Task Process Manager bootstrap file.
 * @var array $definitions bootstrap.php
 */

declare(strict_types=1);

use DI\Container;
use kuaukutsu\poc\task\TaskManager;
use kuaukutsu\poc\task\TaskManagerOptions;
use kuaukutsu\poc\task\tools\TaskManagerOutput;

use function kuaukutsu\poc\task\tools\argument;

require __DIR__ . '/bootstrap.odd.php';

$container = new Container($definitions);

/**
 * @var TaskManager $manager
 * @noinspection PhpUnhandledExceptionInspection
 */
$manager = $container->get(TaskManager::class);
/** @noinspection PhpUnhandledExceptionInspection */
$manager->on($container->get(TaskManagerOutput::class));
/** @noinspection PhpUnhandledExceptionInspection */
$manager->run(
    new TaskManagerOptions(
        bindir: __DIR__,
        heartbeat: (float)argument('heartbeat', 2),
        keeperInterval: (float)argument('iterval', 1),
        queueSize: (int)argument('process', 30),
        handler: 'handler.odd.php',
    )
);
