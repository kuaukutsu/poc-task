<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests\service;

use kuaukutsu\poc\task\dto\StageModel;
use RuntimeException;
use Throwable;

use function kuaukutsu\poc\task\tools\entity_hydrator;

trait StageStorage
{
    private function storage(): string
    {
        return Storage::stage->value;
    }

    /**
     * @return array<string, StageModel>
     * @throws RuntimeException
     */
    private function getData(): array
    {
        $data = @file_get_contents($this->storage());
        if (empty($data)) {
            return [];
        }

        try {
            $items = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return [];
        }

        $list = [];
        foreach ($items as $item) {
            $dto = entity_hydrator(StageModel::class, $item);
            $list[$dto->uuid] = $dto;
        }

        return $list;
    }

    /**
     * @throws RuntimeException
     */
    private function save(array $storage): bool
    {
        try {
            file_put_contents(
                $this->storage(),
                json_encode($storage, JSON_THROW_ON_ERROR),
            );
        } catch (Throwable $exception) {
            throw new RuntimeException($exception->getMessage(), $exception->getCode(), $exception);
        }

        return true;
    }
}
