<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\state;

use kuaukutsu\poc\task\exception\UnsupportedException;
use kuaukutsu\poc\task\tools\SerializerJsonDecorator;
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
                's' => (new SerializerJsonDecorator())->serialize($attributes['response']),
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
                if ($property === 'response' && is_array($value) && isset($value['O'], $value['s'])) {
                    /** @psalm-suppress MixedAssignment */
                    $value = (new SerializerJsonDecorator())->deserialize(
                        (string)$value['s'],
                        (string)$value['O'],
                    );
                }

                $this->{$property} = $value;
            }
        }
    }
}
