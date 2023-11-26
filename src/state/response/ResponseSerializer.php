<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\state\response;

use kuaukutsu\poc\task\tools\SerializerJson;
use kuaukutsu\poc\task\TaskResponseInterface;

trait ResponseSerializer
{
    private function serializeResponse(TaskResponseInterface $response): ResponseWrapper
    {
        if ($response instanceof ResponseContextWrapper) {
            return new ResponseWrapper(
                $response::class,
                (new SerializerJson())->serialize(
                    $response->serialize()
                ),
            );
        }

        return new ResponseWrapper(
            $response::class,
            (new SerializerJson())->serialize($response),
        );
    }

    private function deserializeResponse(ResponseWrapper $wrapper): TaskResponseInterface
    {
        /**
         * @var TaskResponseInterface $response
         */
        $response = (new SerializerJson())->deserialize(
            $wrapper->serializeData,
            $wrapper->class,
        );

        if ($response instanceof ResponseContextWrapper) {
            return $response->deserialize();
        }

        return $response;
    }
}
