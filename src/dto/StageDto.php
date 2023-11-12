<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\dto;

use kuaukutsu\ds\dto\DtoBase;

/**
 * @psalm-immutable
 * @psalm-suppress MissingConstructor
 */
final class StageDto extends DtoBase
{
    /**
     * @var non-empty-string
     */
    public string $uuid;

    /**
     * @var non-empty-string
     */
    public string $taskUuid;

    public int $flag;

    public string $state;

    /**
     * @var non-empty-string
     */
    public string $handler;

    public int $order;

    /**
     * @var non-empty-string
     */
    public string $createdAt;

    /**
     * @var non-empty-string
     */
    public string $updatedAt;
}
