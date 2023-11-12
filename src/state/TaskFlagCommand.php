<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\state;

trait TaskFlagCommand
{
    private TaskFlag $flag;

    final public function getFlag(): int
    {
        return $this->flag->toFlag();
    }

    final public function copyFlag(): TaskFlag
    {
        return new TaskFlag($this->getFlag());
    }

    final public function isReady(): bool
    {
        return $this->flag->isReady();
    }

    final public function isRunning(): bool
    {
        return $this->flag->isRunning();
    }

    final public function isWaiting(): bool
    {
        return $this->flag->isWaiting();
    }

    final public function isPaused(): bool
    {
        return $this->flag->isPaused();
    }

    final public function isSkiped(): bool
    {
        return $this->flag->isSkiped();
    }

    final public function isPromised(): bool
    {
        return $this->flag->isPromised();
    }

    final public function isSuccess(): bool
    {
        return $this->flag->isSuccess();
    }

    final public function isCanceled(): bool
    {
        return $this->flag->isCanceled();
    }

    final public function isCheck(): bool
    {
        return $this->flag->isCheck();
    }

    final public function isFinished(): bool
    {
        return $this->flag->isFinished();
    }

    final public function hasErrors(): bool
    {
        return $this->flag->isError();
    }
}
