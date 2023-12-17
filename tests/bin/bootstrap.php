<?php

declare(strict_types=1);

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleOutputInterface;

use function DI\create;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

$definitions = [
    ConsoleOutputInterface::class => create(ConsoleOutput::class),
];
