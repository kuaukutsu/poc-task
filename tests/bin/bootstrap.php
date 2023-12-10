<?php

declare(strict_types=1);

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use kuaukutsu\poc\task\service\StageCommand;
use kuaukutsu\poc\task\service\StageQuery;
use kuaukutsu\poc\task\service\TaskCommand;
use kuaukutsu\poc\task\service\TaskQuery;
use kuaukutsu\poc\task\tests\service\StageCommandStub;
use kuaukutsu\poc\task\tests\service\StageQueryStub;
use kuaukutsu\poc\task\tests\service\TaskCommandStub;
use kuaukutsu\poc\task\tests\service\TaskQueryStub;

use function DI\autowire;
use function DI\create;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

$definitions = [
    TaskQuery::class => autowire(TaskQueryStub::class),
    TaskCommand::class => autowire(TaskCommandStub::class),
    StageQuery::class => autowire(StageQueryStub::class),
    StageCommand::class => autowire(StageCommandStub::class),
    ConsoleOutputInterface::class => create(ConsoleOutput::class),
];
