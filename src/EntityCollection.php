<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task;

use kuaukutsu\ds\collection\Collection;

/**
 * @extends Collection<EntityWrapper>
 */
final class EntityCollection extends Collection
{
    public function getType(): string
    {
        return EntityWrapper::class;
    }

    public function getChecksum(): string
    {
        $string = '';
        foreach ($this as $item) {
            $string .= serialize($item);
        }

        return md5($string);
    }
}
