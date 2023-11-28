<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\handler;

use kuaukutsu\poc\task\dto\StageModel;
use kuaukutsu\poc\task\state\TaskStatePrepare;
use kuaukutsu\poc\task\TaskStageContext;

final class StageContextFactory
{
    use TaskStatePrepare;

    public function create(StageModel $stage, ?string $previousState = null): TaskStageContext
    {
        $previous = null;
        if ($previousState !== null) {
            $previous = $this->prepareState($previousState);
        }

        return new TaskStageContext(
            task: $stage->taskUuid,
            stage: $stage->uuid,
            previous: $previous,
        );
    }
}
