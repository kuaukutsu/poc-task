<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\service;

use RuntimeException;
use kuaukutsu\poc\task\dto\TaskModel;
use kuaukutsu\poc\task\dto\TaskCreate;
use kuaukutsu\poc\task\dto\TaskState;
use kuaukutsu\poc\task\exception\NotFoundException;
use kuaukutsu\poc\task\EntityUuid;

interface TaskCommand
{
    /**
     * @throws RuntimeException
     */
    public function create(EntityUuid $uuid, TaskCreate $model): TaskModel;

    /**
     * @throws NotFoundException
     * @throws RuntimeException
     */
    public function state(EntityUuid $uuid, TaskState $model): TaskModel;

    /**
     * @throws NotFoundException
     * @throws RuntimeException
     */
    public function remove(EntityUuid $uuid): bool;
}
