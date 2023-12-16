<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task;

use LogicException;
use DI\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use kuaukutsu\poc\task\exception\BuilderException;
use kuaukutsu\poc\task\service\TaskCreator;

final class TaskBuilder
{
    public function __construct(private readonly FactoryInterface $factory)
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
    public function build(EntityNode $node, TaskDraft $draft, ?TaskStageContext $context = null): EntityTask
    {
        try {
            $creator = $this->factoryCreatorByNode($node);
        } catch (ContainerExceptionInterface $exception) {
            throw new BuilderException("[{$draft->getTitle()}] TaskBuilder dependency resolv failed.", $exception);
        }

        return $context === null
            ? $creator->create($draft)
            : $creator->createFromContext($draft, $context);
    }

    /**
     * @throws ContainerExceptionInterface
     */
    private function factoryCreatorByNode(EntityNode $node): TaskCreator
    {
        /**
         * @var TaskCreator
         */
        return $this->factory->make(
            TaskCreator::class,
            [
                'taskQuery' => $node->getTaskQuery(),
                'taskCommand' => $node->getTaskCommand(),
                'stageCommand' => $node->getStageCommand(),
            ]
        );
    }
}
