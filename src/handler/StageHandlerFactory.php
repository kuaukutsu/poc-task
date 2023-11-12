<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\handler;

use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use RuntimeException;
use kuaukutsu\poc\task\dto\StageDto;
use kuaukutsu\poc\task\TaskStageInterface;

final class StageHandlerFactory
{
    public function __construct(private readonly Container $container)
    {
    }

    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function create(StageDto $stage): TaskStageInterface
    {
        /** @var object $incompleteClass */
        $incompleteClass = unserialize(
            $stage->handler,
            [
                'allowed_classes' => false,
            ]
        );

        $config = get_object_vars($incompleteClass);
        if (array_key_exists('__PHP_Incomplete_Class_Name', $config) === false) {
            throw new RuntimeException('Unserialize handler failure.');
        }

        /** @var class-string<TaskStageInterface> $class */
        $class = $config['__PHP_Incomplete_Class_Name'];
        unset($config['__PHP_Incomplete_Class_Name']);

        /**
         * @var TaskStageInterface
         */
        return $this->container->make($class, $config);
    }
}
