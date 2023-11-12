<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\state;

final class TaskFlag
{
    private const FLAG_RUNNING = 1;
    private const FLAG_WAITING = 2;
    private const FLAG_SUCCESS = 4;
    private const FLAG_CANCELED = 8;
    private const FLAG_PROMISED = 16;
    private const FLAG_PAUSED = 32;
    private const FLAG_SKIPED = 64;
    private const FLAG_CHECK = 1024;
    private const FLAG_ERROR = 2048;

    public function __construct(private int $flag = 0)
    {
    }

    public function isReady(): bool
    {
        return $this->flag === 0;
    }

    public function setReady(): self
    {
        $this->flag = 0;
        return $this;
    }

    public function isRunning(): bool
    {
        return ($this->flag & self::FLAG_RUNNING) === self::FLAG_RUNNING;
    }

    public function setRunning(): self
    {
        $this->flag = self::FLAG_RUNNING;
        return $this;
    }

    public function isSuccess(): bool
    {
        return ($this->flag & self::FLAG_SUCCESS) === self::FLAG_SUCCESS;
    }

    public function setSuccess(): self
    {
        $this->flag = self::FLAG_SUCCESS;
        return $this;
    }

    public function isPromised(): bool
    {
        return ($this->flag & self::FLAG_PROMISED) === self::FLAG_PROMISED;
    }

    public function setPromised(): self
    {
        $this->flag = self::FLAG_PROMISED;
        return $this;
    }

    public function isSkiped(): bool
    {
        return ($this->flag & self::FLAG_SKIPED) === self::FLAG_SKIPED;
    }

    public function setSkiped(): self
    {
        $this->flag = self::FLAG_SKIPED;
        return $this;
    }

    public function isWaiting(): bool
    {
        return ($this->flag & self::FLAG_WAITING) === self::FLAG_WAITING;
    }

    public function setWaiting(): self
    {
        $this->flag |= self::FLAG_WAITING;
        return $this;
    }

    public function unsetWaiting(): self
    {
        if ($this->isWaiting()) {
            $this->flag ^= self::FLAG_WAITING;
        }

        return $this;
    }

    public function isPaused(): bool
    {
        return ($this->flag & self::FLAG_PAUSED) === self::FLAG_PAUSED;
    }

    public function setPaused(): self
    {
        $this->flag |= self::FLAG_PAUSED;
        return $this;
    }

    public function unsetPaused(): self
    {
        if ($this->isWaiting()) {
            $this->flag ^= self::FLAG_PAUSED;
        }

        return $this;
    }

    public function isCanceled(): bool
    {
        return ($this->flag & self::FLAG_CANCELED) === self::FLAG_CANCELED;
    }

    public function setCanceled(): self
    {
        $this->flag |= self::FLAG_CANCELED;
        return $this;
    }

    public function isError(): bool
    {
        return ($this->flag & self::FLAG_ERROR) === self::FLAG_ERROR;
    }

    public function setError(): self
    {
        $this->flag |= self::FLAG_ERROR;
        return $this;
    }

    public function unsetError(): self
    {
        if ($this->isError()) {
            $this->flag ^= self::FLAG_ERROR;
        }

        return $this;
    }

    public function isCheck(): bool
    {
        return ($this->flag & self::FLAG_CHECK) === self::FLAG_CHECK;
    }

    public function setCheck(): self
    {
        $this->flag |= self::FLAG_CHECK;
        return $this;
    }

    public function unsetCheck(): self
    {
        if ($this->isCheck()) {
            $this->flag ^= self::FLAG_CHECK;
        }

        return $this;
    }

    public function isFinished(): bool
    {
        return $this->isSuccess()
            || $this->isError()
            || $this->isCanceled()
            || $this->isSkiped();
    }

    public function toFlag(): int
    {
        return $this->flag;
    }
}
