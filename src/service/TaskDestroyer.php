<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\service;

use RuntimeException;
use kuaukutsu\poc\task\exception\NotFoundException;
use kuaukutsu\poc\task\EntityUuid;

final class TaskDestroyer
{
    public function __construct(
        private readonly TaskCommand $taskCommand,
        private readonly StageCommand $stageCommand,
    ) {
    }

    /**
     * @throws RuntimeException
     */
    public function purge(EntityUuid $uuid): void
    {
        try {
            $this->stageCommand->removeByTask($uuid);
        } catch (NotFoundException) {
        }

        try {
            $this->taskCommand->remove($uuid);
        } catch (NotFoundException) {
        }
    }
}
