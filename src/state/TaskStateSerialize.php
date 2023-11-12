<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\state;

use kuaukutsu\poc\task\exception\UnsupportedException;
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
        return get_object_vars($this);
    }

    /**
     * @param array<string, string|int|TaskStateMessage|TaskResponseInterface> $data
     */
    public function __unserialize(array $data): void
    {
        foreach ($data as $property => $value) {
            if (property_exists($this, $property)) {
                $this->{$property} = $value;
            }
        }
    }
}
