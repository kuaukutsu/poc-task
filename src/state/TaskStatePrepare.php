<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\state;

use Exception;
use TypeError;
use kuaukutsu\poc\task\state\response\ResponseWrapper;

trait TaskStatePrepare
{
    /**
     * @throws TypeError unserialize
     * @throws Exception Allowed memory size of N bytes exhausted
     */
    private function prepareState(string $state): TaskStateInterface
    {
        $emptyToken = [
            's:0:"";',
        ];

        $state = trim($state);
        if ($state === '' || in_array($state, $emptyToken, true)) {
            return new TaskStateReady();
        }

        /**
         * @var TaskStateInterface|false $taskState
         * @psalm-suppress InvalidArgument with additional array shape fields (max_depth)
         */
        $taskState = unserialize(
            $state,
            [
                'allowed_classes' => [
                    TaskStateCanceled::class,
                    TaskStateError::class,
                    TaskStateMessage::class,
                    TaskStatePaused::class,
                    TaskStateTerminate::class,
                    TaskStateRelation::class,
                    TaskStateReady::class,
                    TaskStateRunning::class,
                    TaskStateSkip::class,
                    TaskStateSuccess::class,
                    TaskStateWaiting::class,
                    TaskStateDelay::class,
                    ResponseWrapper::class,
                ],
                'max_depth' => 8,
            ]
        );

        if ($taskState instanceof TaskStateInterface) {
            return $taskState;
        }

        throw new TypeError("unserialize(): error.");
    }
}
