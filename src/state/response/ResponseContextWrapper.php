<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\state\response;

use kuaukutsu\poc\task\TaskResponseInterface;
use kuaukutsu\poc\task\tools\SerializerJson;

final class ResponseContextWrapper implements TaskResponseInterface
{
    /**
     * @param TaskResponseInterface[] $success
     * @param TaskResponseInterface[] $failure
     */
    public function __construct(
        private array $success = [],
        private array $failure = [],
    ) {
    }

    public function hasSuccess(): bool
    {
        return $this->success !== [];
    }

    /**
     * @return TaskResponseInterface[]
     */
    public function getSuccess(): array
    {
        return $this->success;
    }

    public function pushSuccessResponse(TaskResponseInterface $response): void
    {
        $this->success[] = $response;
    }

    public function hasFailure(): bool
    {
        return $this->failure !== [];
    }

    /**
     * @return TaskResponseInterface[]
     */
    public function getFailure(): array
    {
        return $this->failure;
    }

    public function pushFailureResponse(TaskResponseInterface $response): void
    {
        $this->failure[] = $response;
    }

    public function serialize(): array
    {
        $callback = static fn(TaskResponseInterface $response): array => [
            'class' => $response::class,
            'serializeData' => (new SerializerJson())->serialize($response),
        ];

        return [
            'success' => array_map($callback, $this->success),
            'failure' => array_map($callback, $this->failure),
        ];
    }

    /** @psalm-suppress all */
    public function deserialize(): self
    {
        $callback = static fn(array $data): TaskResponseInterface => (new SerializerJson())->deserialize(
            $data['serializeData'],
            $data['class'],
        );

        return new self(
            array_map($callback, $this->success),
            array_map($callback, $this->failure),
        );
    }
}
