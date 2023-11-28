<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\service;

use Generator;
use kuaukutsu\poc\task\dto\StageCollection;
use kuaukutsu\poc\task\dto\StageModel;
use kuaukutsu\poc\task\exception\NotFoundException;
use kuaukutsu\poc\task\EntityUuid;

interface StageQuery
{
    /**
     * @throws NotFoundException
     */
    public function getOne(EntityUuid $uuid): StageModel;

    public function findOne(EntityUuid $uuid): ?StageModel;

    /**
     * @return Generator<StageModel>
     */
    public function findByTask(EntityUuid $taskUuid): Generator;

    public function getOpenByTask(EntityUuid $taskUuid): StageCollection;

    public function getPromiseByTask(EntityUuid $taskUuid): StageCollection;

    public function findReadyByTask(EntityUuid $taskUuid): ?StageModel;

    public function findPausedByTask(EntityUuid $taskUuid): ?StageModel;
}
