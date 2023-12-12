<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\state;

final class TaskCommand
{
    private const COMMAND_NULL = '00000000-0000-0000-0000-000000000000';
    private const COMMAND_STOP = '00000000-0000-0000-0000-000000000001';

    /**
     * @param non-empty-string $command
     */
    public function __construct(private readonly string $command = '00000000-0000-0000-0000-000000000000')
    {
    }

    public static function stop(): self
    {
        return new self(self::COMMAND_STOP);
    }

    public function isEmpty(): bool
    {
        return $this->command === self::COMMAND_NULL;
    }

    public function isStop(): bool
    {
        return $this->command === self::COMMAND_STOP;
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
}
