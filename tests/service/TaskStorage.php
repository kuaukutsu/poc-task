<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests\service;

use SQLite3;
use SQLite3Result;
use Throwable;
use RuntimeException;
use kuaukutsu\poc\task\dto\TaskModel;
use kuaukutsu\poc\task\exception\NotFoundException;

use function kuaukutsu\poc\task\tools\entity_hydrator;

trait TaskStorage
{
    private function db(): SQLite3
    {
        $connection = new SQLite3(Storage::task->value);
        $connection->exec(
            <<<SQL
CREATE TABLE IF NOT EXISTS task(
    uuid TEXT PRIMARY KEY, 
    title TEXT,
    flag INT,
    state TEXT,
    options TEXT,
    checksum TEXT,
    created_at TEXT,
    updated_at TEXT
)
SQL
        );

        return $connection;
    }

    /**
     * @throws NotFoundException
     * @throws RuntimeException
     */
    private function getRow(array $conditions, SQLite3 $db): TaskModel
    {
        $data = $this->prepareConditions($conditions, $db)
            ->fetchArray(SQLITE3_ASSOC);

        if ($data === false) {
            throw new NotFoundException('Task Not Found.');
        }

        return $this->prepareData($data);
    }

    /**
     * @return TaskModel[]
     */
    private function getRows(array $conditions, SQLite3 $db): array
    {
        $result = $this->prepareConditions($conditions, $db);

        $rows = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $rows[] = $this->prepareData($row);
        }

        return $rows;
    }

    private function prepareData(array $data): TaskModel
    {
        try {
            $data['options'] = json_decode($data['options'], true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            $data['options'] = [];
        }

        return entity_hydrator(TaskModel::class, $data);
    }

    private function prepareConditions(array $conditions, SQLite3 $db): SQLite3Result
    {
        $stmttConditions = '';
        foreach ($conditions as $key => $value) {
            if ($stmttConditions !== '') {
                $stmttConditions .= ' AND ';
            }

            if (is_array($value)) {
                $stmttConditions .= '( ';
                $row = 0;
                foreach ($value as $ignored) {
                    if ($row > 0) {
                        $stmttConditions .= ' OR ';
                    }

                    $stmttConditions .= $key . '=:' . $key . $row;
                    $row++;
                }
                $stmttConditions .= ') ';
            } else {
                $stmttConditions .= $key . '=:' . $key;
            }
        }

        $stmtt = $db->prepare('SELECT * FROM task WHERE ' . trim($stmttConditions));
        foreach ($conditions as $key => $value) {
            if (is_array($value)) {
                $row = 0;
                foreach ($value as $itemValue) {
                    $stmtt->bindValue(
                        ':' . $key . $row,
                        $itemValue,
                        is_int($itemValue) ? SQLITE3_INTEGER : SQLITE3_TEXT,
                    );
                    $row++;
                }
            } else {
                $stmtt->bindValue(
                    ':' . $key,
                    $value,
                    is_int($value) ? SQLITE3_INTEGER : SQLITE3_TEXT,
                );
            }
        }

        $result = $stmtt->execute();
        if ($result === false) {
            throw new RuntimeException('ModelRead error: ' . $this->connection->lastErrorMsg());
        }

        return $result;
    }
}
