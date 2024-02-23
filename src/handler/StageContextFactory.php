<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\handler;

use kuaukutsu\poc\task\dto\StageModel;
use kuaukutsu\poc\task\state\TaskStatePrepare;
use kuaukutsu\poc\task\TaskStageContext;
use Throwable;

final class StageContextFactory
{
    use TaskStatePrepare;

    public function create(StageModel $stage, ?StageModel $previousStage = null): TaskStageContext
    {
        $previous = null;
        if ($previousStage instanceof StageModel) {
            try {
                $previous = $this->prepareState($previousStage->state);
            } catch (Throwable) {
                // Вероятно, в будущем будем выкидывать исключение, чтобы падать в execute.
            }
        }

        return new TaskStageContext(
            task: $stage->taskUuid,
            stage: $stage->uuid,
            previous: $previous,
        );
    }
}
