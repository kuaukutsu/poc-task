<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\handler;

use InvalidArgumentException;
use TypeError;
use DI\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use kuaukutsu\poc\task\dto\StageModel;
use kuaukutsu\poc\task\EntityHandler;
use kuaukutsu\poc\task\EntityWrapper;
use kuaukutsu\poc\task\exception\BuilderException;

final class StageHandlerFactory
{
    public function __construct(private readonly FactoryInterface $container)
    {
    }

    /**
     * @throws BuilderException
     */
    public function create(StageModel $stage): EntityHandler
    {
        /**
         * @var EntityWrapper $taskStage
         * @psalm-suppress InvalidArgument with additional array shape fields (max_depth)
         */
        $taskStage = unserialize(
            $stage->handler,
            [
                'allowed_classes' => true,
                'max_depth' => 8,
            ]
        );

        if (!$taskStage instanceof EntityWrapper) {
            throw new BuilderException(
                "[$stage->taskUuid] TaskStage handler failure.",
                new InvalidArgumentException(
                    "[$stage->uuid] handler must implement EntityWrapper."
                ),
            );
        }

        try {
            /**
             * @var EntityHandler
             */
            return $this->container->make($taskStage->class, $taskStage->params);
        } catch (ContainerExceptionInterface | TypeError $exception) {
            throw new BuilderException("[$stage->uuid] TaskStageHandler factory failure.", $exception);
        }
    }
}
