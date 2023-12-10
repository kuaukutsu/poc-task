<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests\service;

use RuntimeException;
use kuaukutsu\poc\task\dto\TaskModel;
use kuaukutsu\poc\task\dto\TaskModelCreate;
use kuaukutsu\poc\task\dto\TaskModelState;
use kuaukutsu\poc\task\exception\NotFoundException;
use kuaukutsu\poc\task\service\TaskCommand;
use kuaukutsu\poc\task\EntityUuid;

use function kuaukutsu\poc\task\tools\entity_hydrator;

final class TaskCommandStub implements TaskCommand
{
    use TaskStorage;

    public function __construct(private readonly Mutex $mutex)
    {
    }

    /**
     * @throws RuntimeException
     */
    public function create(EntityUuid $uuid, TaskModelCreate $model): TaskModel
    {
        $dto = entity_hydrator(
            TaskModel::class,
            [
                ...$model->toArray(),
                'uuid' => $uuid->getUuid(),
                'created_at' => gmdate('c'),
                'updated_at' => gmdate('c'),
            ]
        );

        $storage = $this->getDataSafe();
        $storage[] = $dto->toArray();
        $this->saveSafe(
            array_values($storage)
        );

        return $dto;
    }

    public function state(EntityUuid $uuid, TaskModelState $model): TaskModel
    {
        $storage = $this->getDataSafe();
        if (array_key_exists($uuid->getUuid(), $storage) === false) {
            throw new RuntimeException(
                "[{$uuid->getUuid()}] Task not found."
            );
        }

        $dto = entity_hydrator(
            TaskModel::class,
            [
                ...$storage[$uuid->getUuid()]->toArray(),
                ...$model->toArray(),
                'updated_at' => gmdate('c'),
            ]
        );

        $storage[$uuid->getUuid()] = $dto;
        $this->saveSafe(
            array_values($storage)
        );

        return $dto;
    }

    public function terminate(array $indexUuid, TaskModelState $model): bool
    {
        foreach ($indexUuid as $uuid) {
            $this->state(new EntityUuid($uuid), $model);
        }

        return true;
    }

    public function remove(EntityUuid $uuid): bool
    {
        $storage = $this->getDataSafe();
        if (array_key_exists($uuid->getUuid(), $storage) === false) {
            throw new NotFoundException(
                "[{$uuid->getUuid()}] Task not found."
            );
        }

        unset($storage[$uuid->getUuid()]);
        return $this->saveSafe($storage);
    }

    private function saveSafe(array $data): bool
    {
        $this->mutex->lock(3);
        $isSuccess = $this->save($data);
        $this->mutex->unlock();

        return $isSuccess;
    }

    private function getDataSafe(): array
    {
        $this->mutex->lock(10);
        $storage = $this->getData();
        $this->mutex->unlock();

        return $storage;
    }
}
