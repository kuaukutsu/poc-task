<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\service;

use RuntimeException;
use kuaukutsu\poc\task\dto\StageDto;
use kuaukutsu\poc\task\dto\StageModel;
use kuaukutsu\poc\task\exception\NotFoundException;
use kuaukutsu\poc\task\EntityUuid;

interface StageCommand
{
    /**
     * @throws RuntimeException
     */
    public function create(EntityUuid $uuid, StageModel $model): StageDto;

    /**
     * @throws RuntimeException
     */
    public function update(EntityUuid $uuid, StageModel $model): StageDto;

    /**
     * @throws RuntimeException
     */
    public function replace(EntityUuid $uuid, StageDto $model): bool;

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
