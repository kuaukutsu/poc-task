<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task;

interface EntityArrable
{
    /**
     * @return array<string, scalar|array|self|null>
     */
    public function toArray(): array;
}
