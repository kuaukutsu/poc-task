<?php

declare(strict_types=1);

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use kuaukutsu\poc\task\service\StageCommand;
use kuaukutsu\poc\task\service\StageQuery;
use kuaukutsu\poc\task\service\TaskCommand;
use kuaukutsu\poc\task\service\TaskQuery;
use kuaukutsu\poc\task\tests\nodes\even\EvenStageCommand;
use kuaukutsu\poc\task\tests\nodes\even\EvenStageQuery;
use kuaukutsu\poc\task\tests\nodes\even\EvenTaskCommand;
use kuaukutsu\poc\task\tests\nodes\even\EvenTaskQuery;

use function DI\autowire;
use function DI\create;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

$definitions = [
    TaskQuery::class => autowire(EvenTaskQuery::class),
    TaskCommand::class => autowire(EvenTaskCommand::class),
    StageQuery::class => autowire(EvenStageQuery::class),
    StageCommand::class => autowire(EvenStageCommand::class),
    ConsoleOutputInterface::class => create(ConsoleOutput::class),
];
