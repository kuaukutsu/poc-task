<?php

/** @noinspection SqlResolve */

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests\service;

use SQLite3;
use RuntimeException;

/**
 * @note TestData
 */
final class BaseStorage
{
    private ?SQLite3 $db = null;

    private function db(): SQLite3
    {
        if ($this->db === null) {
            $this->db = new SQLite3(':memory:');
            $this->db->exec(
                <<<SQL
CREATE TABLE IF NOT EXISTS memory(
    uuid TEXT PRIMARY KEY, 
    state TEXT
)
SQL
            );
        }

        return $this->db;
    }

    public function set(string $uuid, string $state): void
    {
        $stmt = $this->db()->prepare('INSERT INTO memory VALUES (:uuid, :state)');
        $stmt->bindValue(':uuid', $uuid);
        $stmt->bindValue(':state', $state);
        if ($stmt->execute() === false) {
            throw new RuntimeException('ModelSave error: ' . $this->db()->lastErrorMsg());
        }
    }

    public function get(string $uuid): ?string
    {
        $stmt = $this->db()->prepare('SELECT state FROM memory WHERE uuid=:uuid');
        $stmt->bindValue(':uuid', $uuid);
        $result = $stmt->execute();
        if ($result === false) {
            throw new RuntimeException('ModelRead error: ' . $this->db()->lastErrorMsg());
        }

        $data = $result->fetchArray(SQLITE3_ASSOC);
        if ($data === false || $data === []) {
            return null;
        }

        return $data['state'];
    }

    public function unset(string $uuid): void
    {
        $stmt = $this->db()->prepare('DELETE FROM memory WHERE uuid=:uuid');
        $stmt->bindValue(':uuid', $uuid);
        if ($stmt->execute() === false) {
            throw new RuntimeException('ModelDelete error: ' . $this->db()->lastErrorMsg());
        }
    }
}
