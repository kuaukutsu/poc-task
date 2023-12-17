<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests;

use DI\DependencyException;
use DI\NotFoundException;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use kuaukutsu\poc\task\tools\NullConsoleOutput;
use kuaukutsu\poc\task\service\StageCommand;
use kuaukutsu\poc\task\service\StageQuery;
use kuaukutsu\poc\task\service\TaskCommand;
use kuaukutsu\poc\task\service\TaskQuery;
use kuaukutsu\poc\task\tests\nodes\test\TestStageCommand;
use kuaukutsu\poc\task\tests\nodes\test\TestStageQuery;
use kuaukutsu\poc\task\tests\nodes\test\TestTaskCommand;
use kuaukutsu\poc\task\tests\nodes\test\TestTaskQuery;

use function DI\autowire;
use function DI\create;

/**
 * @template T
 */
trait Container
{
    private static ?\DI\Container $container = null;

    /**
     * @param class-string<T> $id
     * @return T
     * @throws DependencyException
     * @throws NotFoundException
     */
    private static function get(string $id)
    {
        if (self::$container === null) {
            self::$container = new \DI\Container(
                [
                    TaskQuery::class => autowire(TestTaskQuery::class),
                    TaskCommand::class => autowire(TestTaskCommand::class),
                    StageQuery::class => autowire(TestStageQuery::class),
                    StageCommand::class => autowire(TestStageCommand::class),
                    OutputInterface::class => create(NullOutput::class),
                    ConsoleOutputInterface::class => autowire(NullConsoleOutput::class),
                ]
            );
        }

        return self::$container->get($id);
    }
}
