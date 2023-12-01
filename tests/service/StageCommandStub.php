<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests\service;

use RuntimeException;
use kuaukutsu\poc\task\dto\StageModel;
use kuaukutsu\poc\task\dto\StageModelCreate;
use kuaukutsu\poc\task\dto\StageModelState;
use kuaukutsu\poc\task\exception\NotFoundException;
use kuaukutsu\poc\task\state\TaskFlag;
use kuaukutsu\poc\task\service\StageCommand;
use kuaukutsu\poc\task\EntityUuid;

use function kuaukutsu\poc\task\tools\entity_hydrator;

final class StageCommandStub implements StageCommand
{
    use StageStorage;

    public function __construct(private readonly Mutex $mutex)
    {
    }

    public function create(EntityUuid $uuid, StageModelCreate $model): StageModel
    {
        $dto = entity_hydrator(
            StageModel::class,
            [
                ...$model->toArray(),
                'uuid' => $uuid->getUuid(),
                'createdAt' => gmdate('c'),
                'updatedAt' => gmdate('c'),
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

    public function state(EntityUuid $uuid, StageModelState $model): StageModel
    {
        $this->mutex->lock(3);
        $storage = $this->getData();
        if (array_key_exists($uuid->getUuid(), $storage) === false) {
            throw new RuntimeException(
                "[{$uuid->getUuid()}] Stage not found."
            );
        }

        $dto = entity_hydrator(
            StageModel::class,
            [
                ...$storage[$uuid->getUuid()]->toArray(),
                ...$model->toArray(),
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

    public function terminateByTask(array $indexUuid, StageModelState $model): bool
    {
        foreach ($this->getData() as $item) {
            $flag = new TaskFlag($item->flag);
            if ($flag->isRunning() && in_array($item->taskUuid, $indexUuid, true)) {
                $this->state(
                    new EntityUuid($item->uuid),
                    $model
                );
            }
        }

        return true;
    }

    public function removeByTask(EntityUuid $taskUuid): bool
    {
        $storage = array_filter(
            $this->getData(),
            static fn(StageModel $stage): bool => $stage->taskUuid !== $taskUuid->getUuid()
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
