<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\processing;

use Symfony\Component\Process\Process;
use kuaukutsu\poc\task\TaskManagerOptions;

final class ProcessFactory
{
    /**
     * @see https://tldp.org/LDP/abs/html/exitcodes.html
     */
    public const SUCCESS = 0;
    public const ERROR = 1;

    public function create(TaskProcessContext $context, TaskManagerOptions $options): Process
    {
        $cmd = [
            'php',
            $options->handlerEndpoint,
            '--stage=' . $context->stage,
        ];

        if ($context->previous !== null) {
            $cmd[] = '--previous=' . $context->previous;
        }

        return new Process(
            $cmd,
            $options->getBindir(),
            getenv(),
            null,
            $options->handlerTimeout
        );
    }
}
