<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests\service;

use RuntimeException;
use kuaukutsu\poc\task\dto\StageDto;
use kuaukutsu\poc\task\dto\StageModel;
use kuaukutsu\poc\task\exception\NotFoundException;
use kuaukutsu\poc\task\service\StageCommand;
use kuaukutsu\poc\task\EntityUuid;

final class StageCommandStub implements StageCommand
{
    use StageStorage;

    public function __construct(private readonly Mutex $mutex)
    {
    }

    public function create(EntityUuid $uuid, StageModel $model): StageDto
    {
        $dto = StageDto::hydrate(
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

    public function update(EntityUuid $uuid, StageModel $model): StageDto
    {
        $this->mutex->lock(3);
        $storage = $this->getData();
        if (array_key_exists($uuid->getUuid(), $storage) === false) {
            throw new RuntimeException(
                "[{$uuid->getUuid()}] Stage not found."
            );
        }

        $dto = StageDto::hydrate(
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

    public function replace(EntityUuid $uuid, StageDto $model): bool
    {
        $this->mutex->lock(3);
        $storage = $this->getData();
        $storage[$uuid->getUuid()] = $model;

        $this->save(
            array_values($storage)
        );

        $this->mutex->unlock();
        return true;
    }

    public function removeByTask(EntityUuid $taskUuid): bool
    {
        $storage = array_filter(
            $this->getData(),
            static fn(StageDto $stage): bool => $stage->taskUuid !== $taskUuid->getUuid()
        );

        return $this->save($storage);
    }

    public function remove(EntityUuid $uuid): bool
    {
        $storage = $this->getData();
        if (array_key_exists($uuid->getUuid(), $storage) === false) {
            throw new NotFoundException(
                "[{$uuid->getUuid()}] Stage not found."
            );
        }

        unset($storage[$uuid->getUuid()]);
        return $this->save($storage);
    }
}
