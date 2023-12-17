<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests\nodes\odd;

use SQLite3;

final class Connection
{
    public function task(): SQLite3
    {
        return new SQLite3(dirname(__DIR__, 2) . '/data/task.odd.sqlite3');
    }

    public function stage(): SQLite3
    {
        return new SQLite3(dirname(__DIR__, 2) . '/data/stage.odd.sqlite3');
    }
}
