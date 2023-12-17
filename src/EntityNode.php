<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task;

use Psr\Container\ContainerExceptionInterface;
use kuaukutsu\poc\task\service\StageCommand;
use kuaukutsu\poc\task\service\StageQuery;
use kuaukutsu\poc\task\service\TaskCommand;
use kuaukutsu\poc\task\service\TaskQuery;

interface EntityNode
{
    public function label(): string;

    /**
     * @throws ContainerExceptionInterface
     */
    public function getTaskQuery(): TaskQuery;

    /**
     * @throws ContainerExceptionInterface
     */
    public function getTaskCommand(): TaskCommand;

    /**
     * @throws ContainerExceptionInterface
     */
    public function getStageQuery(): StageQuery;

    /**
     * @throws ContainerExceptionInterface
     */
    public function getStageCommand(): StageCommand;
}
