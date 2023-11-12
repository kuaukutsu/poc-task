<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\handler;

use Throwable;
use kuaukutsu\poc\task\dto\StageDto;
use kuaukutsu\poc\task\EntityUuid;
use kuaukutsu\poc\task\service\StageCommand;
use kuaukutsu\poc\task\service\StageQuery;

final class StageHandler
{
    public function __construct(
        private readonly StageQuery $query,
        private readonly StageCommand $stageCommand,
        private readonly StageExecutor $executor,
    ) {
    }

    /**
     * @param non-empty-string $uuid
     * @param non-empty-string|null $previous
     * @return int
     */
    public function handle(string $uuid, ?string $previous = null): int
    {
        try {
            $stage = $this->query->getOne(new EntityUuid($uuid));
            $state = $this->executor->execute($stage);
        } catch (Throwable $exception) {
            $this->stderr($exception->getMessage());
            return 1;
        }

        $stage = StageDto::hydrate(
            [
                ...$stage->toArray(),
                'flag' => $state->getFlag(),
                'state' => serialize($state),
            ]
        );

        try {
            $this->stageCommand->replace(new EntityUuid($uuid), $stage);
        } catch (Throwable $exception) {
            $this->stderr($exception->getMessage());
            return 1;
        }

        $this->stdout($stage->state);
        return 0;
    }

    private function stdout(string $message): void
    {
        fwrite(STDOUT, $message . PHP_EOL);
    }

    private function stderr(string $message): void
    {
        fwrite(STDERR, $message . PHP_EOL);
    }
}
