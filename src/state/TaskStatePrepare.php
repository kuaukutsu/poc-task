<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\state;

use TypeError;
use kuaukutsu\poc\task\state\response\ResponseWrapper;

trait TaskStatePrepare
{
    /**
     * @throws TypeError
     */
    private function prepareState(string $state): TaskStateInterface
    {
        $emptyToken = [
            's:0:"";',
        ];

        if ($state === '' || in_array($state, $emptyToken, true)) {
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
                    TaskStateMessage::class,
                    TaskStatePaused::class,
                    TaskStateRelation::class,
                    TaskStateReady::class,
                    TaskStateRunning::class,
                    TaskStateSkip::class,
                    TaskStateSuccess::class,
                    TaskStateWaiting::class,
                    ResponseWrapper::class,
                ],
            ]
        );
    }
}
