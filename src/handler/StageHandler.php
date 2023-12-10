<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\handler;

use Throwable;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use kuaukutsu\poc\task\dto\StageModel;
use kuaukutsu\poc\task\dto\StageModelState;
use kuaukutsu\poc\task\exception\BuilderException;
use kuaukutsu\poc\task\processing\TaskProcess;
use kuaukutsu\poc\task\service\StageCommand;
use kuaukutsu\poc\task\service\StageQuery;
use kuaukutsu\poc\task\state\TaskStateError;
use kuaukutsu\poc\task\state\TaskStateMessage;
use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\EntityUuid;

final class StageHandler
{
    public function __construct(
        private readonly StageQuery $query,
        private readonly StageCommand $command,
        private readonly StageContextFactory $contextFactory,
        private readonly StageExecutor $executor,
        private readonly ConsoleOutputInterface $output,
    ) {
    }

    /**
     * @param non-empty-string $uuid
     * @param non-empty-string|null $previous
     */
    public function handle(string $uuid, ?string $previous = null): int
    {
        try {
            $stage = $this->query->getOne(new EntityUuid($uuid));
        } catch (Throwable $exception) {
            $this->stderr($exception->getMessage());
            return TaskProcess::ERROR;
        }

        try {
            $state = $this->execute($stage, $previous);
        } catch (Throwable $exception) {
            $state = new TaskStateError(
                new TaskStateMessage(
                    $exception->getMessage(),
                    $exception->getTraceAsString(),
                ),
                $stage->flag,
            );
        }

        try {
            $stage = $this->command->state(
                new EntityUuid($uuid),
                new StageModelState($state),
            );
        } catch (Throwable $exception) {
            $this->stderr($exception->getMessage());
            return TaskProcess::ERROR;
        }

        $this->stdout($stage->state);
        return TaskProcess::SUCCESS;
    }

    /**
     * @param non-empty-string|null $previous
     * @throws BuilderException
     */
    private function execute(StageModel $stage, ?string $previous): TaskStateInterface
    {
        $previousState = null;
        if ($previous !== null) {
            $previousState = $this->query->findOne(new EntityUuid($previous))?->state;
        }

        return $this->executor->execute(
            $stage,
            $this->contextFactory->create($stage, $previousState)
        );
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
