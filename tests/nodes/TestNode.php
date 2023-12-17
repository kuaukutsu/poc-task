<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests\nodes;

use DI\FactoryInterface;
use kuaukutsu\poc\task\EntityNode;
use kuaukutsu\poc\task\service\StageCommand;
use kuaukutsu\poc\task\service\StageQuery;
use kuaukutsu\poc\task\service\TaskCommand;
use kuaukutsu\poc\task\service\TaskQuery;
use kuaukutsu\poc\task\tests\nodes\test\TestStageCommand;
use kuaukutsu\poc\task\tests\nodes\test\TestStageQuery;
use kuaukutsu\poc\task\tests\nodes\test\TestTaskCommand;
use kuaukutsu\poc\task\tests\nodes\test\TestTaskQuery;

final class TestNode implements EntityNode
{
    public function __construct(private readonly FactoryInterface $factory)
    {
    }

    public function label(): string
    {
        return 'test';
    }

    public function getTaskQuery(): TaskQuery
    {
        /**
         * @var TestTaskQuery
         */
        return $this->factory->make(TestTaskQuery::class);
    }

    public function getTaskCommand(): TaskCommand
    {
        /**
         * @var TestTaskCommand
         */
        return $this->factory->make(TestTaskCommand::class);
    }

    public function getStageQuery(): StageQuery
    {
        /**
         * @var TestStageQuery
         */
        return $this->factory->make(TestStageQuery::class);
    }

    public function getStageCommand(): StageCommand
    {
        /**
         * @var TestStageCommand
         */
        return $this->factory->make(TestStageCommand::class);
    }
}
