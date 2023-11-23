<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests\stub;

use kuaukutsu\poc\task\state\response\TaskResponseContext;
use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\state\TaskStateMessage;
use kuaukutsu\poc\task\TaskHandlerBase;
use kuaukutsu\poc\task\TaskStageContext;

final class NumberSaveStageStub extends TaskHandlerBase
{
    public function __construct(public readonly string $name)
    {
    }

    public function handle(TaskStageContext $context): TaskStateInterface
    {
        if ($context->previous === null) {
            return $this->error(
                new TaskStateMessage('NumberSave failure.', 'Previous is empty.'),
                $context,
            );
        }

        if ($context->previous->getFlag()->isError()) {
            return $this->error(
                $context->previous->getMessage(),
                $context,
            );
        }

        /** @var TaskResponseContext $responseContext */
        $responseContext = $context->previous->getResponse();

        if ($responseContext->hasFailure()) {
            return $this->error(
                new TaskStateMessage(
                    $this->name . ' error.',
                    $context->previous->getMessage()->message,
                ),
                $context,
                $responseContext
            );
        }

        $number = 0;
        /** @var DataResponse $response */
        foreach ($responseContext->getSuccess() as $response) {
            $number += $response->response->number;
        }

        return $this->success(
            new TaskStateMessage(
                $this->name . ' success.',
                $context->previous->getMessage()->message,
            ),
            $context,
            new NumberResponse($number, date('c'))
        );
    }
}
