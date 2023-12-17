<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests\nodes\even;

use SQLite3;
use kuaukutsu\poc\task\tests\service\BaseStageCommand;

final class EvenStageCommand extends BaseStageCommand
{
    protected readonly SQLite3 $connection;

    public function __construct(
        protected readonly Mutex $mutex,
        Connection $connection,
    ) {
        $this->connection = $connection->stage();
        $this->connection->exec(
            <<<SQL
CREATE TABLE IF NOT EXISTS stage(
    uuid TEXT PRIMARY KEY, 
    task_uuid TEXT, 
    flag INT,
    state BLOB,
    handler BLOB,
    "order" INT,
    created_at TEXT,
    updated_at TEXT
)
SQL
        );
    }
}
