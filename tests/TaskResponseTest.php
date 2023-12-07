<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests;

use kuaukutsu\poc\task\EntityUuid;
use Psr\Container\ContainerExceptionInterface;
use PHPUnit\Framework\TestCase;
use kuaukutsu\poc\task\handler\StateFactory;
use kuaukutsu\poc\task\state\response\ResponseContextWrapper;
use kuaukutsu\poc\task\state\TaskStateMessage;
use kuaukutsu\poc\task\state\TaskStateSuccess;
use kuaukutsu\poc\task\TaskResponseInterface;

final class TaskResponseTest extends TestCase
{
    use Container;

    /**
     * @throws ContainerExceptionInterface
     */
    public function testSerialize(): void
    {
        $response = static fn(string $name): object => new class ($name) implements TaskResponseInterface {
            public function __construct(public readonly string $name)
            {
            }
        };

        $uuid = new EntityUuid();
        $context = new ResponseContextWrapper();
        $context->pushSuccessResponse(
            $response('test')
        );
        $context->pushSuccessResponse(
            $response('test2')
        );

        $state = new TaskStateSuccess(
            message: new TaskStateMessage('test'),
            response: $context,
        );

        $stateSerialize = serialize($state);
        self::assertNotEmpty($stateSerialize);

        /** @var TaskStateSuccess $stateObject */
        $stateObject = self::get(StateFactory::class)
            ->create($uuid->getUuid(), $stateSerialize);

        self::assertTrue($stateObject->getFlag()->isSuccess());
        self::assertTrue($stateObject->getFlag()->isFinished());

        /** @var ResponseContextWrapper $response */
        $response = $stateObject->getResponse();
        self::assertNotEmpty($response);
        self::assertInstanceOf(ResponseContextWrapper::class, $response);
        self::assertTrue($response->hasSuccess());
        self::assertFalse($response->hasFailure());
        self::assertCount(2, $response->getSuccess());
    }
}
