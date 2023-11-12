<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\state;

use Serializable;
use kuaukutsu\poc\task\exception\UnsupportedException;
use kuaukutsu\poc\task\TaskResponseInterface;

/**
 * Состояние Задачи или отдельного Этапа.
 */
interface TaskStateInterface extends Serializable
{
    public function getFlag(): int;

    /**
     * Для выдачи сообщения в интерфейс клиента.
     */
    public function getMessage(): TaskStateMessage;

    /**
     * Если предполагается ответ.
     */
    public function getResponse(): ?TaskResponseInterface;

    /**
     * @throws UnsupportedException
     */
    public function serialize(): never;

    /**
     * @throws UnsupportedException
     */
    public function unserialize(string $data): never;
}
