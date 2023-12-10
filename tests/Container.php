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
use kuaukutsu\poc\task\tests\service\StageCommandStub;
use kuaukutsu\poc\task\tests\service\StageQueryStub;
use kuaukutsu\poc\task\tests\service\TaskCommandStub;
use kuaukutsu\poc\task\tests\service\TaskQueryStub;

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
                    TaskQuery::class => autowire(TaskQueryStub::class),
                    TaskCommand::class => autowire(TaskCommandStub::class),
                    StageQuery::class => autowire(StageQueryStub::class),
                    StageCommand::class => autowire(StageCommandStub::class),
                    OutputInterface::class => create(NullOutput::class),
                    ConsoleOutputInterface::class => autowire(NullConsoleOutput::class),
                ]
            );
        }

        return self::$container->get($id);
    }
}
