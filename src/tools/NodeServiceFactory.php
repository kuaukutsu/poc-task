<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tools;

use DI\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use kuaukutsu\poc\task\EntityNode;

/**
 * @template T
 */
final readonly class NodeServiceFactory
{
    public function __construct(private FactoryInterface $factory)
    {
    }

    /**
     * @param class-string<T> $serviceName
     * @return T
     * @throws ContainerExceptionInterface
     */
    public function factory(EntityNode $node, string $serviceName)
    {
        /**
         * @var T
         */
        return $this->factory->make(
            $serviceName,
            [
                'taskQuery' => $node->getTaskQuery(),
                'taskCommand' => $node->getTaskCommand(),
                'stageQuery' => $node->getStageQuery(),
                'stageCommand' => $node->getStageCommand(),
            ]
        );
    }
}
