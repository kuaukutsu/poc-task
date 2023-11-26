<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tools;

use RuntimeException;

if (function_exists('kuaukutsu\poc\task\tools\argument') === false) {
    function argument(string $name, string | int | null $default = null): string | int | null
    {
        global $argv;

        foreach ($argv as $item) {
            if (str_starts_with($item, '--')) {
                [$key, $value] = explode('=', ltrim($item, '-'));
                if ($key === $name) {
                    return $value;
                }
            }
        }

        return $default;
    }
}

if (function_exists('kuaukutsu\poc\task\tools\get_stage_uuid') === false) {
    /**
     * @return non-empty-string
     * @throws RuntimeException
     */
    function get_stage_uuid(): string
    {
        $uuid = argument('stage');
        if (is_string($uuid) === false || empty($uuid)) {
            throw new RuntimeException("Stage UUID must be declared.");
        }

        return $uuid;
    }
}

if (function_exists('kuaukutsu\poc\task\tools\get_previous_uuid') === false) {
    /**
     * @return non-empty-string|null
     */
    function get_previous_uuid(): ?string
    {
        $uuid = argument('previous');
        if (is_string($uuid) && empty($uuid) === false) {
            return $uuid;
        }

        return null;
    }
}
