<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\service;

use RuntimeException;
use kuaukutsu\poc\task\exception\NotFoundException;
use kuaukutsu\poc\task\EntityUuid;

final readonly class TaskDestroyer
{
    public function __construct(
        private TaskCommand $taskCommand,
        private StageCommand $stageCommand,
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
