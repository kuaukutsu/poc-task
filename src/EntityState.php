<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task;

use kuaukutsu\poc\task\state\TaskFlag;

interface EntityState
{
    public function getFlag(): int;

    public function copyFlag(): TaskFlag;

    public function isReady(): bool;

    public function isRunning(): bool;

    public function isWaiting(): bool;

    public function isPaused(): bool;

    public function isSkiped(): bool;

    public function isPromised(): bool;

    public function isSuccess(): bool;

    public function isCanceled(): bool;

    public function isFinished(): bool;

    public function hasErrors(): bool;
}
