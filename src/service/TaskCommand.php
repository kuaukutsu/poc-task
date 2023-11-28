<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\service;

use RuntimeException;
use kuaukutsu\poc\task\dto\TaskModel;
use kuaukutsu\poc\task\dto\TaskModelCreate;
use kuaukutsu\poc\task\dto\TaskModelState;
use kuaukutsu\poc\task\exception\NotFoundException;
use kuaukutsu\poc\task\EntityUuid;

interface TaskCommand
{
    /**
     * @throws RuntimeException
     */
    public function create(EntityUuid $uuid, TaskModelCreate $model): TaskModel;

    /**
     * @throws NotFoundException
     * @throws RuntimeException
     */
    public function state(EntityUuid $uuid, TaskModelState $model): TaskModel;

    /**
     * @throws NotFoundException
     * @throws RuntimeException
     */
    public function remove(EntityUuid $uuid): bool;
}
