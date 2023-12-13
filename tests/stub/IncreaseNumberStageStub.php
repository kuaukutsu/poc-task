<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests\stub;

use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\state\TaskStateMessage;
use kuaukutsu\poc\task\TaskHandlerBase;
use kuaukutsu\poc\task\TaskStageContext;

final class IncreaseNumberStageStub extends TaskHandlerBase
{
    public function __construct(
        public readonly string $name = 'Increase number.',
        public readonly int $number = 0,
    ) {
    }

    public function handle(TaskStageContext $context): TaskStateInterface
    {
        // полезная работа
        $number = $this->number + 1;

        if (($number % 3) === 0) {
            sleep(10);
        }

        return $this->success(
            new TaskStateMessage($this->name, 'Увеличиваем число на 1'),
            new DataResponse(
                $this->name,
                new NumberResponse($number, date('c'))
            ),
        );
    }
}
