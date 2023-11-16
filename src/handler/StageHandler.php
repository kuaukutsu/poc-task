<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\handler;

use Throwable;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use kuaukutsu\poc\task\dto\StageDto;
use kuaukutsu\poc\task\exception\NotFoundException;
use kuaukutsu\poc\task\exception\BuilderException;
use kuaukutsu\poc\task\service\StageCommand;
use kuaukutsu\poc\task\service\StageQuery;
use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\EntityUuid;

final class StageHandler
{
    public function __construct(
        private readonly StageQuery $query,
        private readonly StageCommand $stageCommand,
        private readonly StageContextFactory $contextFactory,
        private readonly StageExecutor $executor,
        private readonly ConsoleOutputInterface $output = new ConsoleOutput(),
    ) {
    }

    /**
     * @param non-empty-string $uuid
     * @param non-empty-string|null $previous
     */
    public function handle(string $uuid, ?string $previous = null): void
    {
        try {
            $stage = $this->query->getOne(new EntityUuid($uuid));
            $state = $this->execute($stage, $previous);
        } catch (Throwable $exception) {
            $this->stderr($exception->getMessage());
            exit(1);
        }

        $stage = StageDto::hydrate(
            [
                ...$stage->toArray(),
                'flag' => $state->getFlag()->toValue(),
                'state' => serialize($state),
            ]
        );

        try {
            $this->stageCommand->replace(new EntityUuid($uuid), $stage);
        } catch (Throwable $exception) {
            $this->stderr($exception->getMessage());
            exit(1);
        }

        $this->stdout($stage->state);
        exit(0);
    }

    /**
     * @param non-empty-string|null $previous
     * @throws NotFoundException
     * @throws BuilderException
     */
    private function execute(StageDto $stage, ?string $previous): TaskStateInterface
    {
        $previousState = null;
        if ($previous !== null) {
            try {
                $previousState = $this->query->getOne(new EntityUuid($previous))->state;
            } catch (NotFoundException) {
            }
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
