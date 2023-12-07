<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\handler;

use Throwable;
use kuaukutsu\poc\task\exception\ProcessingException;
use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\state\TaskStatePrepare;

final class StateFactory
{
    use TaskStatePrepare;

    /**
     * @param non-empty-string $uuid
     * @throws ProcessingException
     */
    public function create(string $uuid, string $state): TaskStateInterface
    {
        try {
            return $this->prepareState($state);
        } catch (Throwable $exception) {
            throw new ProcessingException(
                "[$uuid] Task state prepare error: " . $exception->getMessage(),
                $exception,
            );
        }
    }
}
