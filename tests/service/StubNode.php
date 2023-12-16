<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests\service;

use DI\FactoryInterface;
use kuaukutsu\poc\task\EntityNode;
use kuaukutsu\poc\task\service\StageCommand;
use kuaukutsu\poc\task\service\StageQuery;
use kuaukutsu\poc\task\service\TaskCommand;
use kuaukutsu\poc\task\service\TaskQuery;

final class StubNode implements EntityNode
{
    public function __construct(private readonly FactoryInterface $factory)
    {
    }

    public function label(): string
    {
        return 'stub';
    }

    public function getTaskQuery(): TaskQuery
    {
        /**
         * @var TaskQueryStub
         */
        return $this->factory->make(TaskQueryStub::class);
    }

    public function getTaskCommand(): TaskCommand
    {
        /**
         * @var TaskCommandStub
         */
        return $this->factory->make(TaskCommandStub::class);
    }

    public function getStageQuery(): StageQuery
    {
        /**
         * @var StageQueryStub
         */
        return $this->factory->make(StageQueryStub::class);
    }

    public function getStageCommand(): StageCommand
    {
        /**
         * @var StageCommandStub
         */
        return $this->factory->make(StageCommandStub::class);
    }
}
