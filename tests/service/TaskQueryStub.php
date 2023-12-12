<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests\service;

use SQLite3;
use kuaukutsu\poc\task\dto\TaskCollection;
use kuaukutsu\poc\task\dto\TaskModel;
use kuaukutsu\poc\task\exception\NotFoundException;
use kuaukutsu\poc\task\service\TaskQuery;
use kuaukutsu\poc\task\state\TaskFlag;
use kuaukutsu\poc\task\EntityUuid;

final class TaskQueryStub implements TaskQuery
{
    use TaskStorage;

    private readonly SQLite3 $connection;

    public function __construct(private readonly Mutex $mutex)
    {
        $this->connection = $this->db();
    }

    public function getOne(EntityUuid $uuid): TaskModel
    {
        return $this->getRow($uuid->getQueryCondition(), $this->connection);
    }

    public function getReady(int $limit): TaskCollection
    {
        $flag = new TaskFlag();
        $rows = $this->getRows(
            [
                'flag' => $flag->setReady()->toValue(),
            ],
            $limit,
            $this->connection
        );

        $collection = new TaskCollection();
        foreach ($rows as $item) {
            $collection->attach($item);
        }

        return $collection;
    }

    public function getPromise(int $limit): TaskCollection
    {
        $flag = new TaskFlag();
        $rows = $this->getRows(
            [
                'flag' => $flag->setPromised()->toValue(),
            ],
            $limit,
            $this->connection
        );

        $collection = new TaskCollection();
        foreach ($rows as $item) {
            $collection->attach($item);
        }

        return $collection;
    }

    public function getPaused(int $limit): TaskCollection
    {
        $flag = new TaskFlag();
        $rows = $this->getRows(
            [
                'flag' => $flag->unset()->setPaused()->toValue(),
            ],
            $limit,
            $this->connection
        );

        $collection = new TaskCollection();
        foreach ($rows as $item) {
            $collection->attach($item);
        }

        return $collection;
    }

    public function getForgotten(int $limit): TaskCollection
    {
        $flag = new TaskFlag();
        $rows = $this->getRows(
            [
                'flag' => $flag->unset()->setRunning()->toValue(),
            ],
            100,
            $this->connection
        );

        $collection = new TaskCollection();
        foreach ($rows as $item) {
            if ($this->isDateOlderThanOneDay($item->createdAt)) {
                continue;
            }

            $collection->attach($item);
            if ($collection->count() === $limit) {
                return $collection;
            }
        }

        return $collection;
    }

    public function existsOpenByChecksum(string $checksum): bool
    {
        $flag = new TaskFlag();

        try {
            $this->getRow(
                [
                    'checksum' => $checksum,
                    'flag' => [
                        $flag->unset()->setReady()->toValue(),
                        $flag->unset()->setRunning()->toValue(),
                        $flag->unset()->setWaiting()->toValue(),
                    ],
                ],
                $this->connection
            );
        } catch (NotFoundException) {
            return false;
        }

        return true;
    }

    private function isDateOlderThanOneDay(string $createdAt): bool
    {
        return (time() - strtotime($createdAt)) > 86400;
    }
}
