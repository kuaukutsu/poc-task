<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests\service;

use kuaukutsu\poc\task\dto\TaskCollection;
use kuaukutsu\poc\task\dto\TaskModel;
use kuaukutsu\poc\task\EntityUuid;
use kuaukutsu\poc\task\exception\NotFoundException;
use kuaukutsu\poc\task\service\TaskQuery;
use kuaukutsu\poc\task\state\TaskFlag;

final class TaskQueryStub implements TaskQuery
{
    use TaskStorage;

    public function getOne(EntityUuid $uuid): TaskModel
    {
        $storage = $this->getData();
        if (array_key_exists($uuid->getUuid(), $storage) === false) {
            throw new NotFoundException("[{$uuid->getUuid()}] Task not found.");
        }

        return $storage[$uuid->getUuid()];
    }

    public function getReady(int $limit): TaskCollection
    {
        $collection = new TaskCollection();
        foreach ($this->getData() as $item) {
            $flag = new TaskFlag($item->flag);
            if ($flag->isReady() || $flag->isPromised()) {
                $collection->attach($item);
            }

            if ($collection->count() === $limit) {
                return $collection;
            }
        }

        return $collection;
    }

    public function getPaused(int $limit): TaskCollection
    {
        $collection = new TaskCollection();
        foreach ($this->getData() as $item) {
            $flag = new TaskFlag($item->flag);
            if ($flag->isPaused()) {
                $collection->attach($item);
            }

            if ($collection->count() === $limit) {
                return $collection;
            }
        }

        return $collection;
    }

    public function getRunning(int $limit): TaskCollection
    {
        $collection = new TaskCollection();
        foreach ($this->getData() as $item) {
            $flag = new TaskFlag($item->flag);
            if ($flag->isRunning()) {
                $collection->attach($item);
            }

            if ($collection->count() === $limit) {
                return $collection;
            }
        }

        return $collection;
    }

    public function existsOpenByChecksum(string $checksum): bool
    {
        foreach ($this->getData() as $item) {
            if ($item->checksum === $checksum) {
                $flag = new TaskFlag($item->flag);
                if ($flag->isFinished()) {
                    continue;
                }

                return true;
            }
        }

        return false;
    }
}
