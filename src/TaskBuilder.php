<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task;

use Throwable;
use Psr\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
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

    /**
     * @param non-empty-string $title
     */
    public function create(string $title, EntityWrapper ...$stages): TaskDraft
    {
        return new TaskDraft($title, new EntityWrapperCollection(...$stages));
    }

    /**
     * @throws BuilderException
     */
    public function build(TaskDraft $draft, ?TaskStageContext $context = null): EntityTask
    {
        try {
            return $context === null
                ? $this->factory->create($draft)
                : $this->factory->createFromContext($draft, $context);
        } catch (Throwable $exception) {
            throw new BuilderException("[$draft->title] Task builder failed.", $exception);
        }
    }
}
