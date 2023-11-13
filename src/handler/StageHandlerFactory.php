<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\handler;

use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use kuaukutsu\poc\task\dto\StageDto;
use kuaukutsu\poc\task\EntityStage;
use kuaukutsu\poc\task\EntityWrapper;

final class StageHandlerFactory
{
    public function __construct(private readonly Container $container)
    {
    }

    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function create(StageDto $stage): EntityStage
    {
        /** @var EntityWrapper $taskStage */
        $taskStage = unserialize(
            $stage->handler,
            [
                'allowed_classes' => [
                    EntityWrapper::class,
                ],
            ]
        );

        /**
         * @var EntityStage
         */
        return $this->container->make($taskStage->class, $taskStage->params);
    }
}
