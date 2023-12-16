<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\handler;

use Throwable;
use DI\DependencyException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Container\ContainerInterface;
use kuaukutsu\poc\task\dto\TaskOptions;
use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\state\TaskStatePrepare;
use kuaukutsu\poc\task\EntityFinally;

final class TaskFinallyHandler
{
    use TaskStatePrepare;

    public function __construct(private readonly ContainerInterface $container)
    {
    }

    /**
     * @param non-empty-string $uuid Task UUID
     */
    public function handle(string $uuid, TaskOptions $options, TaskStateInterface $state): void
    {
        if ($options->finally === null) {
            return;
        }

        try {
            $this->factory($options->finally)->handle($uuid, $state);
        } catch (Throwable) {
        }
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function factory(string $className): EntityFinally
    {
        /** @var EntityFinally|object $handler */
        $handler = $this->container->get($className);
        if ($handler instanceof EntityFinally) {
            return $handler;
        }

        throw new DependencyException("[$className] must implement EntityFinally.");
    }
}
