<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\service;

use kuaukutsu\poc\task\dto\TaskDto;
use kuaukutsu\poc\task\dto\TaskModel;
use kuaukutsu\poc\task\EntityUuid;

interface TaskCommand
{
    public function create(EntityUuid $uuid, TaskModel $model): TaskDto;

    public function update(EntityUuid $uuid, TaskModel $model): TaskDto;

    public function replace(EntityUuid $uuid, TaskDto $model): bool;

    public function remove(EntityUuid $uuid): bool;
}
