<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\service;

use RuntimeException;
use kuaukutsu\poc\task\dto\TaskDto;
use kuaukutsu\poc\task\dto\TaskModel;
use kuaukutsu\poc\task\exception\NotFoundException;
use kuaukutsu\poc\task\EntityUuid;

interface TaskCommand
{
    /**
     * @throws RuntimeException
     */
    public function create(EntityUuid $uuid, TaskModel $model): TaskDto;

    /**
     * @throws NotFoundException
     * @throws RuntimeException
     */
    public function update(EntityUuid $uuid, TaskModel $model): TaskDto;

    /**
     * @throws NotFoundException
     * @throws RuntimeException
     */
    public function replace(EntityUuid $uuid, TaskDto $model): bool;

    /**
     * @throws NotFoundException
     * @throws RuntimeException
     */
    public function remove(EntityUuid $uuid): bool;
}
