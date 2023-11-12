<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\handler;

use TypeError;
use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use kuaukutsu\poc\task\dto\StageDto;
use kuaukutsu\poc\task\exception\BuilderException;
use kuaukutsu\poc\task\state\TaskStatePrepare;
use kuaukutsu\poc\task\TaskStageContext;

final class StageContextFactory
{
    use TaskStatePrepare;

    public function __construct(private readonly Container $container)
    {
    }

    /**
     * @throws BuilderException
     */
    public function create(StageDto $stage, ?string $previousState = null): TaskStageContext
    {
        $parameters = [
            'task' => $stage->taskUuid,
            'stage' => $stage->uuid,
        ];

        if ($previousState !== null) {
            $parameters['previous'] = $this->prepareState($previousState);
        }

        try {
            /**
             * @var TaskStageContext
             */
            return $this->container->make(TaskStageContext::class, $parameters);
        } catch (DependencyException | NotFoundException | TypeError $exception) {
            throw new BuilderException(
                "[$stage->uuid] TaskStageContext error: " . $exception->getMessage(),
                $exception,
            );
        }
    }
}
