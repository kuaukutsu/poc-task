<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\handler;

use Symfony\Component\Process\Process;
use kuaukutsu\poc\task\TaskManagerOptions;
use kuaukutsu\poc\task\TaskProcessContext;

final class ProcessFactory
{
    public function create(TaskProcessContext $context, TaskManagerOptions $options): Process
    {
        $cmd = [
            'php',
            'handler.php',
            $context->stage,
        ];

        if ($context->previous !== null) {
            $cmd[] = $context->previous;
        }

        return new Process(
            $cmd,
            $options->getBindir(),
            getenv(),
            null,
            300.
        );
    }
}
