<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\service;

use Generator;
use kuaukutsu\poc\task\dto\StageModel;
use kuaukutsu\poc\task\dto\TaskMetrics;
use kuaukutsu\poc\task\exception\NotFoundException;
use kuaukutsu\poc\task\EntityUuid;

interface StageQuery
{
    /**
     * @throws NotFoundException
     */
    public function getOne(EntityUuid $uuid): StageModel;

    public function findOne(EntityUuid $uuid): ?StageModel;

    public function findReadyByTask(EntityUuid $taskUuid): ?StageModel;

    public function findPausedByTask(EntityUuid $taskUuid): ?StageModel;

    public function findForgottenByTask(EntityUuid $taskUuid): ?StageModel;

    public function findPreviousCompletedByTask(EntityUuid $taskUuid, int $stageOrder): ?StageModel;

    /**
     * @return Generator<StageModel>
     */
    public function iterableByTask(EntityUuid $taskUuid): Generator;

    /**
     * @return Generator<StageModel>
     */
    public function iterableOpenByTask(EntityUuid $taskUuid): Generator;

    /**
     * @return Generator<StageModel>
     */
    public function iterableReadyByTask(EntityUuid $taskUuid): Generator;

    public function getMetricsByTask(EntityUuid $taskUuid): TaskMetrics;
}
