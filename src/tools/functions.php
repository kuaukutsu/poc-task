<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tools;

use kuaukutsu\poc\task\EntityArrable;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use TypeError;

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

if (function_exists('kuaukutsu\poc\task\tools\entity_deserialize') === false) {
    /**
     * @template T
     * @param class-string<T> $className
     * @param array<string, scalar|array|null|EntityArrable> $data
     * @return T
     * @throws TypeError
     */
    function entity_deserialize(string $className, array $data)
    {
        $toCamelCase = static function (string $variableName): string {
            $upper = static fn(
                array $matches
            ): string => /** @var array{"word": string} $matches */ strtoupper($matches['word']);
            return preg_replace_callback('~(_)(?<word>[a-z])~', $upper, $variableName);
        };

        $arguments = [];
        foreach ($data as $key => $value) {
            $arguments[$toCamelCase($key)] = $value;
        }

        try {
            /**
             * @var T
             */
            return (new ReflectionClass($className))->newInstanceArgs($arguments);
        } catch (ReflectionException $exception) {
            throw new TypeError(message: $exception->getMessage(), previous: $exception);
        }
    }
}
