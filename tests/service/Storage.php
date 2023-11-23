<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests\service;

enum Storage: string
{
    private const DATA = __DIR__ . '/data';

    case data = self::DATA;

    case task = self::DATA . '/task.json';

    case stage = self::DATA . '/stage.json';
}
