<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests\nodes;

use DI\FactoryInterface;
use kuaukutsu\poc\task\EntityNode;
use kuaukutsu\poc\task\service\StageCommand;
use kuaukutsu\poc\task\service\StageQuery;
use kuaukutsu\poc\task\service\TaskCommand;
use kuaukutsu\poc\task\service\TaskQuery;
use kuaukutsu\poc\task\tests\nodes\odd\OddStageCommand;
use kuaukutsu\poc\task\tests\nodes\odd\OddStageQuery;
use kuaukutsu\poc\task\tests\nodes\odd\OddTaskCommand;
use kuaukutsu\poc\task\tests\nodes\odd\OddTaskQuery;

final class OddNode implements EntityNode
{
    public function __construct(private readonly FactoryInterface $factory)
    {
    }

    public function label(): string
    {
        return 'odd';
    }

    public function getTaskQuery(): TaskQuery
    {
        /**
         * @var OddTaskQuery
         */
        return $this->factory->make(OddTaskQuery::class);
    }

    public function getTaskCommand(): TaskCommand
    {
        /**
         * @var OddTaskCommand
         */
        return $this->factory->make(OddTaskCommand::class);
    }

    public function getStageQuery(): StageQuery
    {
        /**
         * @var OddStageQuery
         */
        return $this->factory->make(OddStageQuery::class);
    }

    public function getStageCommand(): StageCommand
    {
        /**
         * @var OddStageCommand
         */
        return $this->factory->make(OddStageCommand::class);
    }
}
