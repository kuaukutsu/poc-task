<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests\stub;

use kuaukutsu\poc\task\TaskResponseInterface;

final class DataResponse implements TaskResponseInterface
{
    public function __construct(
        public readonly string $name,
        public readonly array $data = [],
        public readonly ?DataResponse $response = null,
    ) {
    }
}
