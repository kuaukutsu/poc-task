<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\handler;

use kuaukutsu\poc\task\state\TaskCommand;
use Throwable;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use kuaukutsu\poc\task\exception\ProcessingException;
use kuaukutsu\poc\task\processing\TaskHandler;
use kuaukutsu\poc\task\processing\TaskProcess;
use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\state\TaskStateSuccess;

final class StageHandler
{
    public function __construct(
        private readonly TaskHandler $handler,
        private readonly ConsoleOutputInterface $output,
    ) {
    }

    /**
     * @param non-empty-string $taskUuid
     * @param non-empty-string $stageUuid
     * @param non-empty-string|null $previous
     */
    public function handle(string $taskUuid, string $stageUuid, ?string $previous = null): int
    {
        if ((new TaskCommand($stageUuid))->isStop()) {
            try {
                $this->stdout(
                    $this->stateSerialize(
                        $this->handler->complete($taskUuid)
                    )
                );

                return TaskProcess::SUCCESS;
            } catch (ProcessingException $exception) {
                $this->stderr($exception->getMessage());
                return TaskProcess::ERROR;
            }
        }

        try {
            $this->stdout(
                $this->stateSerialize(
                    $this->handler->run($taskUuid, $stageUuid, $previous)
                )
            );

            return TaskProcess::SUCCESS;
        } catch (Throwable $exception) {
            $this->stderr($exception->getMessage());
            return TaskProcess::ERROR;
        }
    }

    private function stateSerialize(TaskStateInterface $state): string
    {
        if ($state->getFlag()->isSuccess()) {
            $state = new TaskStateSuccess($state->getMessage());
        }

        return serialize($state);
    }

    private function stdout(string $message): void
    {
        $this->output->writeln($message);
    }

    private function stderr(string $message): void
    {
        $this->output->getErrorOutput()->writeln($message);
    }
}
