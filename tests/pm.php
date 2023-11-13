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

require __DIR__ . '/bootstrap.php';

$container = new Container($definitions);

/**
 * @var TaskManager $manager
 * @noinspection PhpUnhandledExceptionInspection
 */
$manager = $container->get(TaskManager::class);
$manager->on(new TaskManagerOutput());
/** @noinspection PhpUnhandledExceptionInspection */
$manager->run(
    new TaskManagerOptions(
        bindir: __DIR__,
        heartbeat: 5.,
        keeperInterval: 1.,
    )
);
