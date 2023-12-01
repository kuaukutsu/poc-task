<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\service;

use RuntimeException;
use kuaukutsu\poc\task\dto\StageModel;
use kuaukutsu\poc\task\dto\StageModelCreate;
use kuaukutsu\poc\task\dto\StageModelState;
use kuaukutsu\poc\task\exception\NotFoundException;
use kuaukutsu\poc\task\EntityUuid;

interface StageCommand
{
    /**
     * @throws RuntimeException
     */
    public function create(EntityUuid $uuid, StageModelCreate $model): StageModel;

    /**
     * @throws RuntimeException
     */
    public function state(EntityUuid $uuid, StageModelState $model): StageModel;

    /**
     * @param non-empty-string[] $indexUuid
     * @throws RuntimeException
     */
    public function terminateByTask(array $indexUuid, StageModelState $model): bool;

    /**
     * @throws NotFoundException
     * @throws RuntimeException
     */
    public function removeByTask(EntityUuid $taskUuid): bool;

    /**
     * @throws NotFoundException
     * @throws RuntimeException
     */
    public function remove(EntityUuid $uuid): bool;
}
