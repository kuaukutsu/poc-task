<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\dto;

use kuaukutsu\ds\dto\DtoBase;

/**
 * @psalm-immutable
 * @psalm-suppress MissingConstructor
 */
final class TaskModel extends DtoBase
{
    public ?string $title = null;

    public ?int $flag = null;

    public ?string $state = null;

    public ?TaskOptions $options = null;

    public ?string $checksum = null;

    public ?string $created_at = null;

    public ?string $updated_at = null;
}
