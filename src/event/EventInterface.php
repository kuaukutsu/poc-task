<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\event;

interface EventInterface
{
    public function getMessage(): string;
}
