<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\state;

use kuaukutsu\poc\task\exception\UnsupportedException;
use kuaukutsu\poc\task\tools\SerializerJson;
use kuaukutsu\poc\task\TaskResponseInterface;

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
        if (isset($attributes['response']) && is_object($attributes['response'])) {
            $attributes['response'] = [
                'O' => get_class($attributes['response']),
                's' => (new SerializerJson())->serialize($attributes['response']),
            ];
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
                if ($property === 'response' && is_array($value)) {
                    $value = $this->prepareValueResponse($value);
                }

                $this->{$property} = $value;
            }
        }
    }

    private function prepareValueResponse(array $value): TaskResponseInterface | array
    {
        if (isset($value['O'], $value['s'])) {
            /**
             * @var TaskResponseInterface
             */
            return (new SerializerJson())->deserialize(
                (string)$value['s'],
                (string)$value['O'],
            );
        }

        return $value;
    }
}
