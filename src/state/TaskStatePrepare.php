<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\state;

trait TaskStatePrepare
{
    private function prepareState(string $state): TaskStateInterface
    {
        if ($state === '') {
            return new TaskStateReady();
        }

        /**
         * @var TaskStateInterface
         */
        return unserialize(
            $state,
            [
                'allowed_classes' => [
                    TaskStateCanceled::class,
                    TaskStateError::class,
                    TaskStatePaused::class,
                    TaskStatePromised::class,
                    TaskStateReady::class,
                    TaskStateRunning::class,
                    TaskStateSkip::class,
                    TaskStateSuccess::class,
                    TaskStateWaiting::class,
                    TaskStateMessage::class,
                ],
            ]
        );
    }
}
