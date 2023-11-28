<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests\service;

use Generator;
use kuaukutsu\poc\task\dto\StageCollection;
use kuaukutsu\poc\task\dto\StageModel;
use kuaukutsu\poc\task\dto\TaskMetrics;
use kuaukutsu\poc\task\exception\NotFoundException;
use kuaukutsu\poc\task\state\TaskFlag;
use kuaukutsu\poc\task\service\StageQuery;
use kuaukutsu\poc\task\EntityUuid;

final class StageQueryStub implements StageQuery
{
    use StageStorage;

    public function getOne(EntityUuid $uuid): StageModel
    {
        $storage = $this->getData();
        if (array_key_exists($uuid->getUuid(), $storage) === false) {
            throw new NotFoundException("[{$uuid->getUuid()}] Stage not found.");
        }

        return $storage[$uuid->getUuid()];
    }

    public function findOne(EntityUuid $uuid): ?StageModel
    {
        return $this->getData()[$uuid->getUuid()] ?? null;
    }

    /**
     * @return Generator<StageModel>
     */
    public function findByTask(EntityUuid $taskUuid): Generator
    {
        foreach ($this->getData() as $item) {
            if ($item->taskUuid === $taskUuid->getUuid()) {
                yield $item;
            }
        }
    }

    public function getOpenByTask(EntityUuid $taskUuid): StageCollection
    {
        $collection = new StageCollection();
        foreach ($this->getData() as $item) {
            if ($item->taskUuid === $taskUuid->getUuid()) {
                $flag = new TaskFlag($item->flag);
                if ($flag->isReady() || $flag->isRunning() || $flag->isWaiting()) {
                    $collection->attach($item);
                }
            }
        }

        return $collection;
    }

    public function getPromiseByTask(EntityUuid $taskUuid): StageCollection
    {
        $collection = new StageCollection();
        foreach ($this->getData() as $item) {
            if ($item->taskUuid === $taskUuid->getUuid()) {
                $flag = new TaskFlag($item->flag);
                if ($flag->isPromised()) {
                    $collection->attach($item);
                }
            }
        }

        return $collection;
    }

    public function getMetricsByTask(EntityUuid $taskUuid): TaskMetrics
    {
        return new TaskMetrics();
    }

    public function findReadyByTask(EntityUuid $taskUuid): ?StageModel
    {
        foreach ($this->getData() as $item) {
            if ($item->taskUuid === $taskUuid->getUuid()) {
                $flag = new TaskFlag($item->flag);
                if ($flag->isReady()) {
                    return $item;
                }
            }
        }

        return null;
    }

    public function findPausedByTask(EntityUuid $taskUuid): ?StageModel
    {
        foreach ($this->getData() as $item) {
            if ($item->taskUuid === $taskUuid->getUuid()) {
                $flag = new TaskFlag($item->flag);
                if ($flag->isPaused()) {
                    return $item;
                }
            }
        }

        return null;
    }
}
