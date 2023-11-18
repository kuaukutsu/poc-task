<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\service\action;

use kuaukutsu\poc\task\exception\StateTransitionException;
use kuaukutsu\poc\task\state\TaskFlag;

trait TransitionStateTrait
{
    /**
     * @throws StateTransitionException Если переход не возможен.
     */
    final protected function canAccessTransitionState(string $uuid, int $currentState, int $transitionState): void
    {
        if ((new TaskFlag($currentState))->isFinished()) {
            throw new StateTransitionException($uuid, $currentState, $transitionState);
        }

        if (in_array($transitionState, $this->mapTransitionState($currentState), true) === false) {
            throw new StateTransitionException($uuid, $currentState, $transitionState);
        }
    }

    /**
     * @return int[]
     */
    private function mapTransitionState(int $state): array
    {
        $map = [
            (new TaskFlag())->setReady()->toValue() => [
                (new TaskFlag())->setRunning()->toValue(),
                (new TaskFlag())->setSkiped()->toValue(),
                (new TaskFlag())->setCanceled()->toValue(),
                (new TaskFlag())->setError()->toValue(),
            ],
            (new TaskFlag())->setPromised()->toValue() => [
                (new TaskFlag())->setRunning()->toValue(),
                (new TaskFlag())->setSkiped()->toValue(),
                (new TaskFlag())->setCanceled()->toValue(),
                (new TaskFlag())->setError()->toValue(),
            ],
            (new TaskFlag())->setRunning()->toValue() => [
                (new TaskFlag())->setSuccess()->toValue(),
                (new TaskFlag())->setSkiped()->toValue(),
                (new TaskFlag())->setCanceled()->toValue(),
                (new TaskFlag())->setWaiting()->toValue(),
                (new TaskFlag())->setRunning()->setPaused()->toValue(),
                (new TaskFlag())->setRunning()->setCanceled()->toValue(),
                (new TaskFlag())->setRunning()->setError()->toValue(),
            ],
            (new TaskFlag())->setPaused()->toValue() => [
                (new TaskFlag())->setRunning()->toValue(),
                (new TaskFlag())->setSkiped()->toValue(),
                (new TaskFlag())->setCanceled()->toValue(),
                (new TaskFlag())->setError()->toValue(),
            ],
        ];

        return $map[$state] ?? [];
    }
}
