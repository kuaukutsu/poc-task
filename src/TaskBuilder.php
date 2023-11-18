<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task;

use kuaukutsu\poc\task\exception\BuilderException;
use kuaukutsu\poc\task\service\TaskCreator;

final class TaskBuilder
{
    public function __construct(private readonly TaskCreator $factory)
    {
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
        return $context === null
            ? $this->factory->create($draft)
            : $this->factory->createFromContext($draft, $context);
    }
}
