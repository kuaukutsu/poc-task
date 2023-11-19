#!/usr/bin/env php
<?php

/**
 * Task Stage handler file.
 * @var array $definitions bootstrap.php
 */

declare(strict_types=1);

use DI\Container;
use Ramsey\Uuid\Uuid;
use kuaukutsu\poc\task\handler\StageHandler;

use function kuaukutsu\poc\task\tools\argument;

require __DIR__ . '/bootstrap.php';

/** @var non-empty-string|null $stageUuid */
$stageUuid = argument('stage');
/** @var non-empty-string|null $previousUuid */
$previousUuid = argument('previous');
if ($stageUuid === null || Uuid::isValid($stageUuid) === false) {
    throw new RuntimeException("Stage UUID must be declared.");
}

$container = new Container($definitions);

/**
 * @psalm-var StageHandler $handler
 * @noinspection PhpUnhandledExceptionInspection
 */
$handler = $container->get(StageHandler::class);
$handler->handle($stageUuid, $previousUuid);
