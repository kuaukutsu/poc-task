<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task;

use Serializable;
use kuaukutsu\poc\task\exception\UnsupportedException;

final class EntityWrapper implements Serializable
{
    /**
     * @param class-string<EntityHandler> $class
     * @param array<string, string|int|string[]|int[]|array|object|null> $params Конфигурация объекта.
     */
    public function __construct(
        public readonly string $class,
        public readonly array $params = [],
    ) {
    }

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
        return [
            'class' => $this->class,
            'params' => $this->params,
        ];
    }

    /**
     * @param array{
     *     "class": class-string<EntityHandler>,
     *     "params": array<string, string|int|string[]|int[]|array|object|null>} $data
     */
    public function __unserialize(array $data): void
    {
        $this->class = $data['class'];
        $this->params = $data['params'];
    }
}
