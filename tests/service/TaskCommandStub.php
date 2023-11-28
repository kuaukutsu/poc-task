<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests\service;

use RuntimeException;
use kuaukutsu\poc\task\dto\TaskModel;
use kuaukutsu\poc\task\dto\TaskCreate;
use kuaukutsu\poc\task\dto\TaskUpdate;
use kuaukutsu\poc\task\exception\NotFoundException;
use kuaukutsu\poc\task\service\TaskCommand;
use kuaukutsu\poc\task\EntityUuid;

use function kuaukutsu\poc\task\tools\entity_deserialize;

final class TaskCommandStub implements TaskCommand
{
    use TaskStorage;

    public function __construct(private readonly Mutex $mutex)
    {
    }

    /**
     * @throws RuntimeException
     */
    public function create(EntityUuid $uuid, TaskCreate $model): TaskModel
    {
        $dto = entity_deserialize(
            TaskModel::class,
            [
                ...$model->toArray(),
                'uuid' => $uuid->getUuid(),
                'created_at' => gmdate('c'),
                'updated_at' => gmdate('c'),
            ]
        );

        $this->mutex->lock(3);
        $storage = $this->getData();
        $storage[] = $dto->toArray();
        $this->save(
            array_values($storage)
        );

        $this->mutex->unlock();
        return $dto;
    }

    public function update(EntityUuid $uuid, TaskUpdate $model): TaskModel
    {
        $this->mutex->lock(3);
        $storage = $this->getData();
        if (array_key_exists($uuid->getUuid(), $storage) === false) {
            throw new RuntimeException(
                "[{$uuid->getUuid()}] Task not found."
            );
        }

        $dto = entity_deserialize(
            TaskModel::class,
            [
                ...$storage[$uuid->getUuid()]->toArray(),
                ...$model->toArray(),
                'updated_at' => gmdate('c'),
            ]
        );

        $storage[$uuid->getUuid()] = $dto;
        $this->save(
            array_values($storage)
        );

        $this->mutex->unlock();
        return $dto;
    }

    public function replace(EntityUuid $uuid, TaskModel $model): bool
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
            throw new NotFoundException(
                "[{$uuid->getUuid()}] Task not found."
            );
        }

        unset($storage[$uuid->getUuid()]);
        return $this->save($storage);
    }
}
