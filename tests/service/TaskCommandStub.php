<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests\service;

use kuaukutsu\poc\task\dto\TaskDto;
use kuaukutsu\poc\task\dto\TaskModel;
use kuaukutsu\poc\task\EntityUuid;
use kuaukutsu\poc\task\service\TaskCommand;
use RuntimeException;

final class TaskCommandStub implements TaskCommand
{
    use TaskStorage;

    public function __construct(private readonly Mutex $mutex)
    {
    }

    /**
     * @throws RuntimeException
     */
    public function create(EntityUuid $uuid, TaskModel $model): TaskDto
    {
        $dto = TaskDto::hydrate(
            [
                ...$model->toArrayRecursive(),
                'uuid' => $uuid->getUuid(),
                'createdAt' => gmdate('c'),
                'updatedAt' => gmdate('c'),
            ]
        );

        $this->mutex->lock(3);
        $storage = $this->getData();
        $storage[] = $dto->toArrayRecursive();
        $this->save(
            array_values($storage)
        );

        $this->mutex->unlock();
        return $dto;
    }

    public function update(EntityUuid $uuid, TaskModel $model): TaskDto
    {
        $this->mutex->lock(3);
        $storage = $this->getData();
        if (array_key_exists($uuid->getUuid(), $storage) === false) {
            throw new RuntimeException(
                "[{$uuid->getUuid()}] Task not found."
            );
        }

        $dto = TaskDto::hydrate(
            [
                ...$storage[$uuid->getUuid()]->toArrayRecursive(),
                ...$model->toArrayRecursive(),
                'updatedAt' => gmdate('c'),
            ]
        );

        $storage[$uuid->getUuid()] = $dto;
        $this->save(
            array_values($storage)
        );

        $this->mutex->unlock();
        return $dto;
    }

    public function replace(EntityUuid $uuid, TaskDto $model): bool
    {
        $this->mutex->lock(3);
        $storage = $this->getData();
        $storage[$uuid->getUuid()] = $model;
        $isSave = $this->save(
            array_values($storage)
        );

        $this->mutex->unlock();
        return $isSave;
    }

    public function remove(EntityUuid $uuid): bool
    {
        $storage = $this->getData();
        if (array_key_exists($uuid->getUuid(), $storage) === false) {
            throw new RuntimeException(
                "[{$uuid->getUuid()}] Task not found."
            );
        }

        unset($storage[$uuid->getUuid()]);
        return $this->save($storage);
    }
}
