<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests\stub;

use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\state\TaskStateMessage;
use kuaukutsu\poc\task\EntityWrapper;
use kuaukutsu\poc\task\TaskBuilder;
use kuaukutsu\poc\task\TaskHandlerBase;
use kuaukutsu\poc\task\TaskStageContext;

final class NumberHandlerStageStub extends TaskHandlerBase
{
    public function __construct(private readonly TaskBuilder $builder)
    {
    }

    public function handle(TaskStageContext $context): TaskStateInterface
    {
        if ($context->previous === null) {
            return $this->error(
                new TaskStateMessage('NumberHandler failure.', 'Previous is empty.'),
            );
        }

        if ($context->previous->getFlag()->isError()) {
            return $this->error(
                $context->previous->getMessage(),
                $context->previous->getResponse(),
            );
        }

        /** @var DataResponse $response */
        $response = $context->previous->getResponse();

        $task = $this->builder->create('Nested Task: ' . $context->task);
        $task->addStage(
            new EntityWrapper(
                class: IncreaseNumberStageStub::class,
                params: [
                    'name' => 'Nested one: ' . $response->name,
                    'number' => $response->response->number,
                ],
            ),
            new EntityWrapper(
                class: IncreaseNumberStageStub::class,
                params: [
                    'name' => 'Nested two: ' . $response->name,
                    'number' => $response->response->number + 1,
                ],
            ),
            new EntityWrapper(
                class: IncreaseNumberStageStub::class,
                params: [
                    'name' => 'Nested three: ' . $response->name,
                    'number' => $response->response->number + 2,
                ],
            ),
            new EntityWrapper(
                class: IncreaseNumberStageStub::class,
                params: [
                    'name' => 'Nested three: ' . $response->name,
                    'number' => $response->response->number + 3,
                ],
            ),
            new EntityWrapper(
                class: IncreaseNumberStageStub::class,
                params: [
                    'name' => 'Nested three: ' . $response->name,
                    'number' => $response->response->number + 4,
                ],
            ),
            new EntityWrapper(
                class: IncreaseNumberStageStub::class,
                params: [
                    'name' => 'Nested three: ' . $response->name,
                    'number' => $response->response->number + 5,
                ],
            ),
            new EntityWrapper(
                class: IncreaseNumberStageStub::class,
                params: [
                    'name' => 'Nested three: ' . $response->name,
                    'number' => $response->response->number + 6,
                ],
            ),
            new EntityWrapper(
                class: IncreaseNumberStageStub::class,
                params: [
                    'name' => 'Nested three: ' . $response->name,
                    'number' => $response->response->number + 7,
                ],
            ),
            new EntityWrapper(
                class: IncreaseNumberStageStub::class,
                params: [
                    'name' => 'Nested three: ' . $response->name,
                    'number' => $response->response->number + 8,
                ],
            ),
        );

        return $this->wait(
            $this->builder->build($task, $context),
            $context,
        );
    }
}
