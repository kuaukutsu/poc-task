<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\event;

enum Event: string
{
    case LoopExit = 'loop-exit-event';

    case LoopTick = 'loop-tick-event';

    case LoopTimeout = 'loop-timeout-event';

    case LoopException = 'loop-exception-event';

    case ProcessPush = 'process-push-event';

    case ProcessPull = 'process-pull-event';

    case ProcessStop = 'process-stop-event';

    case ProcessDelay = 'process-delay-event';

    case ProcessSuccess = 'process-success-event';

    case ProcessError = 'process-error-event';

    case ProcessException = 'process-error-exception';

    case ProcessTimeout = 'process-timeout-event';
}
