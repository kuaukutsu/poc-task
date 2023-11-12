<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\dto;

use kuaukutsu\ds\dto\DtoBase;

/**
 * @psalm-immutable
 * @psalm-suppress MissingConstructor
 */
final class StageModel extends DtoBase
{
    public ?string $task_uuid = null;

    public ?int $flag = null;

    public ?string $state = null;

    public ?string $handler = null;

    public ?int $order = null;

    public ?string $created_at = null;

    public ?string $updated_at = null;
}
