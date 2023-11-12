<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task;

use ReflectionClass;
use ReflectionProperty;
use kuaukutsu\poc\task\exception\UnsupportedException;

trait TaskStageSerialize
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
        $properties = (new ReflectionClass($this))
            ->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED);

        $structure = [];
        foreach ($properties as $property) {
            /** @psalm-suppress MixedAssignment */
            $structure[$property->name] = $property->getValue($this);
        }

        return $structure;
    }

    /**
     * @param array<string, scalar> $data
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
