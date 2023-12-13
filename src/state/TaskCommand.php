<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\state;

use Stringable;

final class TaskCommand implements Stringable
{
    private const COMMAND_NULL = '00000000-0000-0000-0000-000000000000';
    private const COMMAND_STOP = '00000000-0000-0000-0000-000000000001';
    private const COMMAND_STATE = '00000000-0000-0000-0000-000000000002';

    /**
     * @param non-empty-string $command
     */
    public function __construct(private readonly string $command = self::COMMAND_NULL)
    {
    }

    public static function stop(): self
    {
        return new self(self::COMMAND_STOP);
    }

    public static function state(): self
    {
        return new self(self::COMMAND_STATE);
    }

    public function isEmpty(): bool
    {
        return $this->command === self::COMMAND_NULL;
    }

    public function isStop(): bool
    {
        return $this->command === self::COMMAND_STOP;
    }

    public function isState(): bool
    {
        return $this->command === self::COMMAND_STATE;
    }

    public function unset(): self
    {
        return new self();
    }

    /**
     * @return non-empty-string
     */
    public function toValue(): string
    {
        return $this->command;
    }

    public function __toString(): string
    {
        return $this->toValue();
    }
}
