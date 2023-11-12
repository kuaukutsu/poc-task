<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\event;

enum Event: string
{
    case LoopExit = 'loop-exit-event';

    case LoopTick = 'loop-tick-event';

    case LoopTimeout = 'loop-timeout-event';

    case StagePush = 'process-push-event';

    case StagePull = 'process-pull-event';

    case StageStop = 'process-stop-event';

    case StageSuccess = 'process-success-event';

    case StageError = 'process-error-event';

    case StageTimeout = 'process-timeout-event';
}
