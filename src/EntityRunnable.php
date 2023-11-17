<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task;

use kuaukutsu\poc\task\exception\BuilderException;
use kuaukutsu\poc\task\exception\StateTransitionException;
use kuaukutsu\poc\task\exception\RunnerException;
use kuaukutsu\poc\task\state\TaskStateInterface;

interface EntityRunnable
{
    /**
     * Выполнить задачу.
     *
     * @throws RunnerException
     * @throws StateTransitionException
     */
    public function run(): TaskStateInterface;

    /**
     * Остановить выполнение задачи.
     * Если в задаче нет доступных этапов, то закрываем задачу.
     *
     * @throws BuilderException
     * @throws StateTransitionException
     */
    public function stop(): TaskStateInterface;

    /**
     * Отменить выполнение задачи/шага.
     * Если в задаче есть отменённый шаг, то задача не может считаться выполненной.
     *
     * @throws BuilderException
     * @throws StateTransitionException
     */
    public function cancel(): TaskStateInterface;

    /**
     * Ставит задачу на паузу.
     * Если задача не была запущена, то просто не запускается.
     *
     * @throws BuilderException
     * @throws StateTransitionException
     */
    public function pause(): TaskStateInterface;
}
