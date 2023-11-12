<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\service;

use kuaukutsu\poc\task\dto\StageDto;
use kuaukutsu\poc\task\dto\StageModel;
use kuaukutsu\poc\task\EntityUuid;

interface StageCommand
{
    public function create(EntityUuid $uuid, StageModel $model): StageDto;

    public function update(EntityUuid $uuid, StageModel $model): StageDto;

    public function replace(EntityUuid $uuid, StageDto $model): bool;

    public function removeByTask(EntityUuid $taskUuid): bool;

    public function remove(EntityUuid $uuid): bool;
}
