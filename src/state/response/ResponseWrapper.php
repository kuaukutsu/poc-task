<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\state\response;

use kuaukutsu\poc\task\TaskResponseInterface;
use kuaukutsu\poc\task\tools\SerializerJson;

final class ResponseWrapper
{
    /**
     * @param class-string<TaskResponseInterface> $class
     * @param string $serializeData
     */
    public function __construct(
        public readonly string $class,
        public readonly string $serializeData,
    ) {
    }

    public static function serialize(TaskResponseInterface $response): self
    {
        if ($response instanceof TaskResponseContext) {
            return new self(
                $response::class,
                (new SerializerJson())->serialize(
                    $response->serialize()
                ),
            );
        }

        return new self(
            $response::class,
            (new SerializerJson())->serialize($response),
        );
    }

    public function deserialize(): TaskResponseInterface
    {
        /**
         * @var TaskResponseInterface $response
         */
        $response = (new SerializerJson())->deserialize(
            $this->serializeData,
            $this->class,
        );

        if ($response instanceof TaskResponseContext) {
            return $response->deserialize();
        }

        return $response;
    }
}
