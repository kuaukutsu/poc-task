<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\state;

use kuaukutsu\poc\task\exception\UnsupportedException;
use kuaukutsu\poc\task\state\response\ResponseContextWrapper;
use kuaukutsu\poc\task\state\response\ResponseWrapper;
use kuaukutsu\poc\task\TaskResponseInterface;
use kuaukutsu\poc\task\tools\SerializerJson;

trait TaskStateSerialize
{
    /**
     * @throws UnsupportedException
     */
    public function serialize(): never
    {
        throw new UnsupportedException();
    }

    /**
     * @throws UnsupportedException
     */
    public function unserialize(string $data): never
    {
        throw new UnsupportedException();
    }

    public function __serialize(): array
    {
        /** @var array<string, mixed> $attributes */
        $attributes = get_object_vars($this);
        if (isset($attributes['response']) && $attributes['response'] instanceof TaskResponseInterface) {
            $attributes['response'] = $this->serializeResponse($attributes['response']);
        }

        return $attributes;
    }

    /**
     * @param array<string, array|string|int|TaskStateMessage|TaskResponseInterface> $data
     */
    public function __unserialize(array $data): void
    {
        foreach ($data as $property => $value) {
            if (property_exists($this, $property)) {
                if ($property === 'response' && $value instanceof ResponseWrapper) {
                    $value = $this->deserializeResponse($value);
                }

                $this->{$property} = $value;
            }
        }
    }

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
