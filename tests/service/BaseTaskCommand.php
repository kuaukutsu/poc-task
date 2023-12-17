<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests\service;

use SQLite3;
use Throwable;
use RuntimeException;
use kuaukutsu\poc\task\dto\TaskModel;
use kuaukutsu\poc\task\dto\TaskModelCreate;
use kuaukutsu\poc\task\dto\TaskModelState;
use kuaukutsu\poc\task\exception\NotFoundException;
use kuaukutsu\poc\task\service\TaskCommand;
use kuaukutsu\poc\task\state\TaskFlag;
use kuaukutsu\poc\task\EntityUuid;

/**
 * @property-read SQLite3 $connection
 */
abstract class BaseTaskCommand implements TaskCommand
{
    use TaskStorage;

    /**
     * @throws RuntimeException
     * @throws NotFoundException
     */
    public function create(EntityUuid $uuid, TaskModelCreate $model): TaskModel
    {
        $this->mutex->lock(5);
        $stmt = $this->connection->prepare(
            'INSERT INTO task VALUES (
                          :uuid, :title, :flag, :state, :options, :checksum, :created_at, :updated_at)'
        );
        $stmt->bindValue(':uuid', $uuid->getUuid());
        $stmt->bindValue(':title', $model->title);
        $stmt->bindValue(':flag', $model->flag, SQLITE3_INTEGER);
        $stmt->bindValue(':state', $model->state, SQLITE3_BLOB);
        $stmt->bindValue(':checksum', $model->checksum);
        $stmt->bindValue(':created_at', gmdate('c'));
        $stmt->bindValue(':updated_at', gmdate('c'));

        try {
            $stmt->bindValue(':options', json_encode($model->options->toArray(), JSON_THROW_ON_ERROR));
        } catch (Throwable) {
            $stmt->bindValue(':options', '[]');
        }

        if ($stmt->execute() === false) {
            $this->mutex->unlock();
            throw new RuntimeException('ModelSave error: ' . $this->connection->lastErrorMsg());
        }

        $row = $this->getRow($uuid->getQueryCondition(), $this->connection);
        $this->mutex->unlock();

        return $row;
    }

    public function state(EntityUuid $uuid, TaskModelState $model): TaskModel
    {
        $this->mutex->lock(5);
        $stmt = $this->connection->prepare(
            'UPDATE task SET flag=:flag, state=:state, updated_at=:updated_at WHERE uuid=:uuid'
        );

        $stmt->bindValue(':flag', $model->flag, SQLITE3_INTEGER);
        $stmt->bindValue(':state', $model->state, SQLITE3_BLOB);
        $stmt->bindValue(':updated_at', gmdate('c'));
        $stmt->bindValue(':uuid', $uuid->getUuid());
        if ($stmt->execute() === false) {
            $this->mutex->unlock();
            throw new RuntimeException('ModelSave error: ' . $this->connection->lastErrorMsg());
        }

        $row = $this->getRow($uuid->getQueryCondition(), $this->connection);
        $this->mutex->unlock();

        return $row;
    }

    public function terminate(array $indexUuid, TaskModelState $model): bool
    {
        $this->mutex->lock(5);
        foreach ($indexUuid as $uuid) {
            $stmt = $this->connection->prepare(
                'UPDATE task SET flag=:flag, state=:state, updated_at=:updated_at WHERE uuid=:uuid AND flag=:running'
            );

            $stmt->bindValue(':flag', $model->flag, SQLITE3_INTEGER);
            $stmt->bindValue(':state', $model->state, SQLITE3_BLOB);
            $stmt->bindValue(':updated_at', gmdate('c'));
            $stmt->bindValue(':uuid', $uuid);
            $stmt->bindValue(':running', (new TaskFlag())->setRunning()->toValue(), SQLITE3_INTEGER);
            if ($stmt->execute() === false) {
                $this->mutex->unlock();
                throw new RuntimeException('ModelSave error: ' . $this->connection->lastErrorMsg());
            }
        }

        $this->mutex->unlock();

        return true;
    }

    public function remove(EntityUuid $uuid): bool
    {
        $this->mutex->lock(5);
        $stmt = $this->connection->prepare('DELETE FROM task WHERE uuid=:uuid');
        $stmt->bindValue(':uuid', $uuid->getUuid());
        if ($stmt->execute() === false) {
            $this->mutex->unlock();
            throw new RuntimeException('ModelDelete error: ' . $this->connection->lastErrorMsg());
        }

        $this->mutex->unlock();

        return true;
    }
}
