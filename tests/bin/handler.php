#!/usr/bin/env php
<?php

/**
 * Task Stage handler file.
 * @var array $definitions bootstrap.php
 */

declare(strict_types=1);

use DI\Container;
use kuaukutsu\poc\task\handler\StageHandler;

use function kuaukutsu\poc\task\tools\get_previous_uuid;
use function kuaukutsu\poc\task\tools\get_stage_uuid;

require __DIR__ . '/bootstrap.php';

$container = new Container($definitions);

/**
 * @psalm-var StageHandler $handler
 * @noinspection PhpUnhandledExceptionInspection
 */
$handler = $container->get(StageHandler::class);
$exitCode = $handler->handle(get_stage_uuid(), get_previous_uuid());
exit($exitCode);
