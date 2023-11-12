<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests\service;

use kuaukutsu\poc\task\dto\TaskCollection;
use kuaukutsu\poc\task\dto\TaskDto;
use kuaukutsu\poc\task\EntityUuid;
use kuaukutsu\poc\task\exception\NotFoundException;
use kuaukutsu\poc\task\service\TaskQuery;
use kuaukutsu\poc\task\state\TaskFlag;

final class TaskQueryStub implements TaskQuery
{
    use TaskStorage;

    public function getOne(EntityUuid $uuid): TaskDto
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
        }

        return $collection;
    }
}
