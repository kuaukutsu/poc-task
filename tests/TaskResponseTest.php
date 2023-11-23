<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests;

use Psr\Container\ContainerExceptionInterface;
use PHPUnit\Framework\TestCase;
use kuaukutsu\poc\task\handler\StateFactory;
use kuaukutsu\poc\task\state\response\TaskResponseContext;
use kuaukutsu\poc\task\state\TaskStateMessage;
use kuaukutsu\poc\task\state\TaskStateSuccess;
use kuaukutsu\poc\task\TaskResponseInterface;
use kuaukutsu\poc\task\EntityUuid;

final class TaskResponseTest extends TestCase
{
    use Container;

    /**
     * @throws ContainerExceptionInterface
     */
    public function testSerialize(): void
    {
        $uuid = new EntityUuid();
        $response = static fn(string $name): object => new class ($name) implements TaskResponseInterface {
            public function __construct(public readonly string $name)
            {
            }
        };

        $context = new TaskResponseContext();
        $context->pushSuccessResponse(
            $response('test')
        );
        $context->pushSuccessResponse(
            $response('test2')
        );

        $state = new TaskStateSuccess(
            uuid: $uuid->getUuid(),
            message: new TaskStateMessage('test'),
            response: $context,
        );

        $stateSerialize = serialize($state);
        self::assertNotEmpty($stateSerialize);

        /** @var TaskStateSuccess $stateObject */
        $stateObject = self::get(StateFactory::class)->create($stateSerialize);
        self::assertEquals($uuid->getUuid(), $stateObject->uuid);
        self::assertTrue($stateObject->getFlag()->isSuccess());
        self::assertTrue($stateObject->getFlag()->isFinished());

        /** @var TaskResponseContext $response */
        $response = $stateObject->getResponse();
        self::assertNotEmpty($response);
        self::assertInstanceOf(TaskResponseContext::class, $response);
        self::assertTrue($response->hasSuccess());
        self::assertFalse($response->hasFailure());
        self::assertCount(2, $response->getSuccess());
    }
}
