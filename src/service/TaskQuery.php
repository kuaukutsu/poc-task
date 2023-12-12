<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\service;

use kuaukutsu\poc\task\dto\TaskCollection;
use kuaukutsu\poc\task\dto\TaskModel;
use kuaukutsu\poc\task\exception\NotFoundException;
use kuaukutsu\poc\task\EntityUuid;

interface TaskQuery
{
    /**
     * @throws NotFoundException
     */
    public function getOne(EntityUuid $uuid): TaskModel;

    /**
     * @param positive-int $limit
     */
    public function getReady(int $limit): TaskCollection;

    /**
     * @param positive-int $limit
     */
    public function getPromise(int $limit): TaskCollection;

    /**
     * @param positive-int $limit
     */
    public function getPaused(int $limit): TaskCollection;

    /**
     * @param positive-int $limit
     */
    public function getForgotten(int $limit): TaskCollection;

    public function existsOpenByChecksum(string $checksum): bool;
}
