<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\state;

use kuaukutsu\poc\task\TaskResponseInterface;

final class TaskResponseContext implements TaskResponseInterface
{
    /**
     * @param array<non-empty-string, TaskResponseInterface> $success
     * @param array<non-empty-string, TaskResponseInterface> $failure
     */
    public function __construct(
        private array $success = [],
        private array $failure = [],
    ) {
    }

    /**
     * @return array<non-empty-string, TaskResponseInterface>
     */
    public function getSuccess(): array
    {
        return $this->success;
    }

    /**
     * @param non-empty-string $uuid
     */
    public function pushSuccessResponse(string $uuid, TaskResponseInterface $response): void
    {
        $this->success[$uuid] = $response;
    }

    /**
     * @return array<non-empty-string, TaskResponseInterface>
     */
    public function getFailure(): array
    {
        return $this->failure;
    }

    /**
     * @param non-empty-string $uuid
     */
    public function pushFailureResponse(string $uuid, TaskResponseInterface $response): void
    {
        $this->failure[$uuid] = $response;
    }
}
