<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\handler;

use TypeError;
use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use kuaukutsu\poc\task\dto\StageDto;
use kuaukutsu\poc\task\EntityHandler;
use kuaukutsu\poc\task\EntityWrapper;
use kuaukutsu\poc\task\exception\BuilderException;

final class StageHandlerFactory
{
    public function __construct(private readonly Container $container)
    {
    }

    /**
     * @throws BuilderException
     */
    public function create(StageDto $stage): EntityHandler
    {
        /**
         * @var EntityWrapper $taskStage
         */
        $taskStage = unserialize(
            $stage->handler,
            [
                'allowed_classes' => [
                    EntityWrapper::class,
                ],
            ]
        );

        try {
            /**
             * @var EntityHandler
             */
            return $this->container->make($taskStage->class, $taskStage->params);
        } catch (DependencyException | NotFoundException | TypeError $exception) {
            throw new BuilderException(
                "[$stage->uuid] TaskStageHandler factory error: " . $exception->getMessage(),
                $exception,
            );
        }
    }
}
