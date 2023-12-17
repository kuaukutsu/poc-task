<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests\service;

use SQLite3;
use Generator;
use kuaukutsu\poc\task\dto\StageModel;
use kuaukutsu\poc\task\dto\TaskMetrics;
use kuaukutsu\poc\task\exception\NotFoundException;
use kuaukutsu\poc\task\state\TaskFlag;
use kuaukutsu\poc\task\service\StageQuery;
use kuaukutsu\poc\task\EntityUuid;

/**
 * @property-read SQLite3 $connection
 */
abstract class BaseStageQuery implements StageQuery
{
    use StageStorage;

    public function getOne(EntityUuid $uuid): StageModel
    {
        return $this->getRow($uuid->getQueryCondition(), $this->connection);
    }

    public function findOne(EntityUuid $uuid): ?StageModel
    {
        try {
            return $this->getRow($uuid->getQueryCondition(), $this->connection);
        } catch (NotFoundException) {
            return null;
        }
    }

    public function indexReadyByTask(EntityUuid $taskUuid, int $limit): array
    {
        $flag = new TaskFlag();
        $rows = $this->getRows(
            [
                'task_uuid' => $taskUuid->getUuid(),
                'flag' => [
                    $flag->unset()->setReady()->toValue(),
                    $flag->unset()->setPaused()->toValue(),
                ],
            ],
            $limit,
            $this->connection
        );

        $index = [];
        foreach ($rows as $item) {
            $index[] = $item->uuid;
        }

        return $index;
    }

    /**
     * @return Generator<StageModel>
     */
    public function iterableByTask(EntityUuid $taskUuid): Generator
    {
        $rows = $this->getRows(
            [
                'task_uuid' => $taskUuid->getUuid(),
            ],
            0,
            $this->connection
        );

        foreach ($rows as $item) {
            yield $item;
        }
    }

    public function findReadyByTask(EntityUuid $taskUuid): ?StageModel
    {
        try {
            return $this->getRow(
                [
                    'task_uuid' => $taskUuid->getUuid(),
                    'flag' => (new TaskFlag())->setReady()->toValue(),
                ],
                $this->connection
            );
        } catch (NotFoundException) {
            return null;
        }
    }

    public function findPausedByTask(EntityUuid $taskUuid): ?StageModel
    {
        $flag = new TaskFlag();

        try {
            return $this->getRow(
                [
                    'task_uuid' => $taskUuid->getUuid(),
                    'flag' => $flag->unset()->setPaused()->toValue(),
                ],
                $this->connection
            );
        } catch (NotFoundException) {
            return null;
        }
    }

    public function findForgottenByTask(EntityUuid $taskUuid): ?StageModel
    {
        $flag = new TaskFlag();

        try {
            return $this->getRow(
                [
                    'task_uuid' => $taskUuid->getUuid(),
                    'flag' => $flag->unset()->setRunning()->toValue(),
                ],
                $this->connection
            );
        } catch (NotFoundException) {
            return null;
        }
    }

    public function findPreviousCompletedByTask(EntityUuid $taskUuid, int $stageOrder): ?StageModel
    {
        $flag = new TaskFlag();

        try {
            return $this->getRow(
                [
                    'task_uuid' => $taskUuid->getUuid(),
                    'order' => --$stageOrder,
                    'flag' => [
                        $flag->unset()->setSuccess()->toValue(),
                        $flag->unset()->setError()->toValue(),
                        $flag->unset()->setCanceled()->toValue(),
                        $flag->unset()->setSkipped()->toValue(),
                    ],
                ],
                $this->connection
            );
        } catch (NotFoundException) {
            return null;
        }
    }

    public function existsOpenByTask(EntityUuid $taskUuid): bool
    {
        $flag = new TaskFlag();

        try {
            $this->getRow(
                [
                    'task_uuid' => $taskUuid->getUuid(),
                    'flag' => [
                        $flag->unset()->setReady()->toValue(),
                        $flag->unset()->setPaused()->toValue(),
                        $flag->unset()->setRunning()->toValue(),
                        $flag->unset()->setRunning()->setPaused()->toValue(),
                    ],
                ],
                $this->connection
            );
        } catch (NotFoundException) {
            return false;
        }

        return true;
    }

    public function getMetricsByTask(EntityUuid $taskUuid): TaskMetrics
    {
        return new TaskMetrics();
    }
}
