<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\service;

use kuaukutsu\poc\task\dto\StageCollection;
use kuaukutsu\poc\task\dto\StageDto;
use kuaukutsu\poc\task\exception\NotFoundException;
use kuaukutsu\poc\task\EntityUuid;

interface StageQuery
{
    /**
     * @throws NotFoundException
     */
    public function getOne(EntityUuid $uuid): StageDto;

    public function getPromiseByTask(EntityUuid $taskUuid): StageCollection;

    public function findReadyByTask(EntityUuid $taskUuid): ?StageDto;

    public function findPausedByTask(EntityUuid $taskUuid): ?StageDto;
}
