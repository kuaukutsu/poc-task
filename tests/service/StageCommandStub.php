<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests\service;

use SQLite3;
use RuntimeException;
use kuaukutsu\poc\task\dto\StageModel;
use kuaukutsu\poc\task\dto\StageModelCreate;
use kuaukutsu\poc\task\dto\StageModelState;
use kuaukutsu\poc\task\state\TaskFlag;
use kuaukutsu\poc\task\service\StageCommand;
use kuaukutsu\poc\task\EntityUuid;

final class StageCommandStub implements StageCommand
{
    use StageStorage;

    private readonly SQLite3 $connection;

    public function __construct(private readonly Mutex $mutex)
    {
        $this->connection = $this->db();
    }

    public function create(EntityUuid $uuid, StageModelCreate $model): StageModel
    {
        $this->mutex->lock(5);
        $stmt = $this->connection->prepare(
            'INSERT INTO stage VALUES (
                          :uuid, :uuid_task, :flag, :state, :handler, :order, :created_at, :updated_at)'
        );
        $stmt->bindValue(':uuid', $uuid->getUuid());
        $stmt->bindValue(':uuid_task', $model->taskUuid);
        $stmt->bindValue(':flag', $model->flag, SQLITE3_INTEGER);
        $stmt->bindValue(':state', $model->state, SQLITE3_BLOB);
        $stmt->bindValue(':handler', $model->handler, SQLITE3_BLOB);
        $stmt->bindValue(':order', $model->order, SQLITE3_INTEGER);
        $stmt->bindValue(':created_at', gmdate('c'));
        $stmt->bindValue(':updated_at', gmdate('c'));
        if ($stmt->execute() === false) {
            $this->mutex->unlock();
            throw new RuntimeException('ModelSave error: ' . $this->connection->lastErrorMsg());
        }

        $row = $this->getRow($uuid->getQueryCondition(), $this->connection);
        $this->mutex->unlock();

        return $row;
    }

    public function state(EntityUuid $uuid, StageModelState $model): StageModel
    {
        $this->mutex->lock(5);
        $stmt = $this->connection->prepare(
            'UPDATE stage SET 
                 flag=:flag, state=:state, updated_at=:updated_at WHERE uuid=:uuid'
        );

        $stmt->bindValue(':uuid', $uuid->getUuid());
        $stmt->bindValue(':flag', $model->flag, SQLITE3_INTEGER);
        $stmt->bindValue(':state', $model->state, SQLITE3_BLOB);
        $stmt->bindValue(':updated_at', gmdate('c'));
        if ($stmt->execute() === false) {
            $this->mutex->unlock();
            throw new RuntimeException('ModelSave error: ' . $this->connection->lastErrorMsg());
        }

        $row = $this->getRow($uuid->getQueryCondition(), $this->connection);
        $this->mutex->unlock();

        return $row;
    }

    public function stateByTask(EntityUuid $uuid, StageModelState $model): bool
    {
        $this->mutex->lock(5);
        $stmt = $this->connection->prepare(
            'UPDATE stage SET 
                 flag=:flag, state=:state, updated_at=:updated_at 
             WHERE task_uuid=:uuid AND flag=:running'
        );

        $stmt->bindValue(':flag', $model->flag, SQLITE3_INTEGER);
        $stmt->bindValue(':state', $model->state, SQLITE3_BLOB);
        $stmt->bindValue(':updated_at', gmdate('c'));
        $stmt->bindValue(':uuid', $uuid->getUuid());
        $stmt->bindValue(':running', (new TaskFlag())->setRunning()->toValue(), SQLITE3_INTEGER);
        if ($stmt->execute() === false) {
            $this->mutex->unlock();
            throw new RuntimeException('ModelSave error: ' . $this->connection->lastErrorMsg());
        }

        $this->mutex->unlock();

        return true;
    }

    public function terminateByTask(array $indexUuid, StageModelState $model): bool
    {
        foreach ($indexUuid as $taskUuid) {
            $this->stateByTask(new EntityUuid($taskUuid), $model);
        }

        return true;
    }

    public function removeByTask(EntityUuid $uuid): bool
    {
        $this->mutex->lock(5);
        $stmt = $this->connection->prepare('DELETE FROM stage WHERE task_uuid=:uuid');
        $stmt->bindValue(':uuid', $uuid->getUuid());
        if ($stmt->execute() === false) {
            $this->mutex->unlock();
            throw new RuntimeException('ModelDelete error: ' . $this->connection->lastErrorMsg());
        }

        $this->mutex->unlock();

        return true;
    }

    public function remove(EntityUuid $uuid): bool
    {
        $this->mutex->lock(5);
        $stmt = $this->connection->prepare('DELETE FROM stage WHERE uuid=:uuid');
        $stmt->bindValue(':uuid', $uuid->getUuid());
        if ($stmt->execute() === false) {
            $this->mutex->unlock();
            throw new RuntimeException('ModelDelete error: ' . $this->connection->lastErrorMsg());
        }

        $this->mutex->unlock();

        return true;
    }
}
