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

require __DIR__ . '/bootstrap.php';

$container = new Container($definitions);

/**
 * @var TaskManager $manager
 * @noinspection PhpUnhandledExceptionInspection
 */
$manager = $container->get(TaskManager::class);

/** @noinspection PhpUnhandledExceptionInspection */
$manager->run(
    new TaskManagerOptions(
        bindir: __DIR__,
        heartbeat: 5.,
        keeperInterval: 2.,
    )
);
