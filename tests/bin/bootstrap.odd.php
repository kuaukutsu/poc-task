<?php

declare(strict_types=1);

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use kuaukutsu\poc\task\service\StageCommand;
use kuaukutsu\poc\task\service\StageQuery;
use kuaukutsu\poc\task\service\TaskCommand;
use kuaukutsu\poc\task\service\TaskQuery;
use kuaukutsu\poc\task\tests\nodes\odd\OddStageCommand;
use kuaukutsu\poc\task\tests\nodes\odd\OddStageQuery;
use kuaukutsu\poc\task\tests\nodes\odd\OddTaskCommand;
use kuaukutsu\poc\task\tests\nodes\odd\OddTaskQuery;

use function DI\autowire;
use function DI\create;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

$definitions = [
    TaskQuery::class => autowire(OddTaskQuery::class),
    TaskCommand::class => autowire(OddTaskCommand::class),
    StageQuery::class => autowire(OddStageQuery::class),
    StageCommand::class => autowire(OddStageCommand::class),
    ConsoleOutputInterface::class => create(ConsoleOutput::class),
];
