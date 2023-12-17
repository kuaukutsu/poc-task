<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests\nodes;

use DI\FactoryInterface;
use kuaukutsu\poc\task\EntityNode;
use kuaukutsu\poc\task\service\StageCommand;
use kuaukutsu\poc\task\service\StageQuery;
use kuaukutsu\poc\task\service\TaskCommand;
use kuaukutsu\poc\task\service\TaskQuery;
use kuaukutsu\poc\task\tests\nodes\even\EvenStageCommand;
use kuaukutsu\poc\task\tests\nodes\even\EvenStageQuery;
use kuaukutsu\poc\task\tests\nodes\even\EvenTaskCommand;
use kuaukutsu\poc\task\tests\nodes\even\EvenTaskQuery;

final class EvenNode implements EntityNode
{
    public function __construct(private readonly FactoryInterface $factory)
    {
    }

    public function label(): string
    {
        return 'even';
    }

    public function getTaskQuery(): TaskQuery
    {
        /**
         * @var EvenTaskQuery
         */
        return $this->factory->make(EvenTaskQuery::class);
    }

    public function getTaskCommand(): TaskCommand
    {
        /**
         * @var EvenTaskCommand
         */
        return $this->factory->make(EvenTaskCommand::class);
    }

    public function getStageQuery(): StageQuery
    {
        /**
         * @var EvenStageQuery
         */
        return $this->factory->make(EvenStageQuery::class);
    }

    public function getStageCommand(): StageCommand
    {
        /**
         * @var EvenStageCommand
         */
        return $this->factory->make(EvenStageCommand::class);
    }
}
