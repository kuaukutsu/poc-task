<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests\stub;

use kuaukutsu\ds\dto\DtoBase;

final class TestWrapperDto extends DtoBase implements TestWrapperInterface
{
    public string $name;

    public function getName(): string
    {
        return $this->name;
    }
}
