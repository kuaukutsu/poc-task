<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\dto;

use kuaukutsu\ds\dto\DtoBase;

/**
 * @psalm-immutable
 * @psalm-suppress MissingConstructor
 */
final class TaskDto extends DtoBase
{
    /**
     * @var non-empty-string
     */
    public string $uuid;

    /**
     * @var non-empty-string
     */
    public string $title;

    public int $flag;

    public string $state;

    public array $options = [];

    /**
     * @var non-empty-string
     */
    public string $checksum;

    /**
     * @var non-empty-string
     */
    public string $createdAt;

    /**
     * @var non-empty-string
     */
    public string $updatedAt;
}
