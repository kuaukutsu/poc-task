<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\handler;

use TypeError;
use DI\DependencyException;
use DI\NotFoundException;
use DI\Container;
use kuaukutsu\poc\task\dto\TaskDto;
use kuaukutsu\poc\task\exception\BuilderException;
use kuaukutsu\poc\task\state\TaskStatePrepare;
use kuaukutsu\poc\task\TaskInterface;
use kuaukutsu\poc\task\Task;

final class TaskFactory
{
    use TaskStatePrepare;

    public function __construct(private readonly Container $container)
    {
    }

    /**
     * @throws BuilderException
     */
    public function create(TaskDto $dto): TaskInterface
    {
        try {
            /**
             * @var TaskInterface
             */
            return $this->container->make(
                Task::class,
                [
                    'uuid' => $dto->uuid,
                    'title' => $dto->title,
                    'state' => $this->prepareState($dto->state),
                ]
            );
        } catch (DependencyException | NotFoundException | TypeError $exception) {
            throw new BuilderException(
                "[$dto->uuid] TaskFactory error: " . $exception->getMessage(),
                $exception,
            );
        }
    }
}
