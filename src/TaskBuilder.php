<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task;

use LogicException;
use kuaukutsu\poc\task\exception\BuilderException;
use kuaukutsu\poc\task\service\TaskCreator;

final class TaskBuilder
{
    public function __construct(private readonly TaskCreator $creator)
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
     * @throws LogicException
     */
    public function build(TaskDraft $draft, ?TaskStageContext $context = null): EntityTask
    {
        return $context === null
            ? $this->creator->create($draft)
            : $this->creator->createFromContext($draft, $context);
    }
}
