<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests\stub;

use kuaukutsu\poc\task\EntityFinally;
use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\tests\service\BaseStorage;

final readonly class TestParamsFinally implements EntityFinally
{
    public function __construct(
        public string $name,
        private BaseStorage $storage,
    ) {
    }

    public function handle(string $uuid, TaskStateInterface $state): void
    {
        $this->storage->set($uuid, $this->name . $state->getMessage()->message);
    }
}
