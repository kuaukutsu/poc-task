<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\processing;

use Symfony\Component\Process\Process;
use kuaukutsu\poc\task\TaskManagerOptions;

final class TaskProcessFactory
{
    public function create(TaskProcessContext $context, TaskManagerOptions $options): TaskProcess
    {
        $cmd = [
            'php',
            $options->handler,
            '--task=' . $context->task,
            '--stage=' . $context->stage,
        ];

        if ($context->previous !== null) {
            $cmd[] = '--previous=' . $context->previous;
        }

        return new TaskProcess(
            hash: $context->getHash(),
            task: $context->task,
            stage: $context->stage,
            process: new Process(
                $cmd,
                $options->getBindir(),
                getenv(),
                null,
                $context->options->timeout
            ),
        );
    }
}
