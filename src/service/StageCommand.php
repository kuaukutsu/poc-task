<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\service;

use RuntimeException;
use kuaukutsu\poc\task\dto\StageModel;
use kuaukutsu\poc\task\dto\StageCreate;
use kuaukutsu\poc\task\dto\StageUpdate;
use kuaukutsu\poc\task\exception\NotFoundException;
use kuaukutsu\poc\task\EntityUuid;

interface StageCommand
{
    /**
     * @throws RuntimeException
     */
    public function create(EntityUuid $uuid, StageCreate $model): StageModel;

    /**
     * @throws RuntimeException
     */
    public function update(EntityUuid $uuid, StageUpdate $model): StageModel;

    /**
     * @throws RuntimeException
     */
    public function replace(EntityUuid $uuid, StageModel $model): bool;

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
