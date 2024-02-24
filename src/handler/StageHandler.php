<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\handler;

use Throwable;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use kuaukutsu\poc\task\processing\TaskHandler;
use kuaukutsu\poc\task\processing\TaskProcess;
use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\state\TaskStateSuccess;
use kuaukutsu\poc\task\state\TaskCommand;

final readonly class StageHandler
{
    public function __construct(
        private TaskHandler $handler,
        private ConsoleOutputInterface $output,
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
                        $this->handler->complete($taskUuid, $stageUuid)
                    )
                );

                return TaskProcess::SUCCESS;
            } catch (Throwable $exception) {
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
