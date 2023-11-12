<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task;

use Ramsey\Uuid\Uuid;

final class EntityUuid
{
    /**
     * @var non-empty-string
     */
    private readonly string $uuid;

    /**
     * @param non-empty-string|null $uuid
     */
    public function __construct(?string $uuid = null)
    {
        $this->uuid = $uuid ?? $this->generateUuid();
    }

    /**
     * @return non-empty-string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * @return array{"uuid": non-empty-string}
     */
    public function getQueryCondition(): array
    {
        return ['uuid' => $this->uuid];
    }

    public function __toString(): string
    {
        return $this->getUuid();
    }

    /**
     * @return non-empty-string
     */
    private function generateUuid(): string
    {
        /**
         * @var non-empty-string
         */
        return Uuid::uuid7()->toString();
    }
}
