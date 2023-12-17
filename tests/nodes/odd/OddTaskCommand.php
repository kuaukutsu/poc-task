<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests\nodes\odd;

use SQLite3;
use kuaukutsu\poc\task\tests\service\BaseTaskCommand;

final class OddTaskCommand extends BaseTaskCommand
{
    protected readonly SQLite3 $connection;

    public function __construct(
        protected readonly Mutex $mutex,
        Connection $connection,
    ) {
        $this->connection = $connection->task();
        $this->connection->exec(
            <<<SQL
CREATE TABLE IF NOT EXISTS task(
    uuid TEXT PRIMARY KEY, 
    title TEXT,
    flag INT,
    state BLOB,
    options TEXT,
    checksum TEXT,
    created_at TEXT,
    updated_at TEXT
)
SQL
        );
    }
}
