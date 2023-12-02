<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests\stub;

use kuaukutsu\poc\task\state\response\ResponseContextWrapper;
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
            );
        }

        if ($context->previous->getFlag()->isError()) {
            return $this->error(
                $context->previous->getMessage(),
            );
        }

        /** @var ResponseContextWrapper $responseContext */
        $responseContext = $context->previous->getResponse();

        if ($responseContext->hasFailure()) {
            return $this->error(
                new TaskStateMessage(
                    $this->name . ' error.',
                    $context->previous->getMessage()->message,
                ),
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
            new NumberResponse($number, date('c'))
        );
    }
}
