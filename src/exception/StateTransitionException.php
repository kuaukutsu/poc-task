<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\exception;

use RuntimeException;

/**
 * Для обозначения ситуации когда мы не можем переключиться между двумя статусами.
 */
final class StateTransitionException extends RuntimeException
{
    public function __construct(string $uuid, int $currentState, int $transitionState)
    {
        parent::__construct("[$uuid] Is impossible to move from [$currentState] to status [$transitionState].");
    }
}
