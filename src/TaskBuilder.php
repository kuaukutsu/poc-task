<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task;

use Throwable;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use kuaukutsu\poc\task\exception\BuilderException;
use kuaukutsu\poc\task\service\TaskCreator;

final class TaskBuilder
{
    private readonly TaskCreator $factory;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(ContainerInterface $container)
    {
        /** @psalm-var TaskCreator */
        $this->factory = $container->get(TaskCreator::class);
    }

    public function create(string $title, TaskStageInterface ...$stages): TaskDraft
    {
        return new TaskDraft($title, new TaskStageCollection(...$stages));
    }

    /**
     * @throws BuilderException
     */
    public function build(TaskDraft $draft, ?TaskStageInterface $stageRelation = null): TaskInterface
    {
        try {
            return $this->factory->create($draft);
        } catch (Throwable $exception) {
            throw new BuilderException("[$draft->title] Task builder failed.", $exception);
        }
    }
}
