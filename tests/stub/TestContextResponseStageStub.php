<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests\stub;

use kuaukutsu\poc\task\state\response\ResponseContextWrapper;
use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\state\TaskStateMessage;
use kuaukutsu\poc\task\TaskHandlerBase;
use kuaukutsu\poc\task\TaskStageContext;

final class TestContextResponseStageStub extends TaskHandlerBase
{
    public function handle(TaskStageContext $context): TaskStateInterface
    {
        $response = $this->preparePrevious($context)->getResponse();
        if ($response instanceof ResponseContextWrapper) {
            return $this->success(
                new TaskStateMessage('Success'),
                $response,
            );
        }

        return $this->error(
            new TaskStateMessage('Error', 'Response must implement ResponseContextWrapper'),
            $response,
        );
    }
}
