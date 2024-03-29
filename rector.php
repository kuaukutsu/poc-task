<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths(
        [
            __DIR__ . '/src',
        ]
    );

    $rectorConfig->skip(
        [
            __DIR__ . '/src/tools/functions.php',
        ]
    );

    // register a single rule
    $rectorConfig->rule(InlineConstructorDefaultToPropertyRector::class);

    // define sets of rules
    $rectorConfig->sets(
        [
            SetList::CODE_QUALITY,
            SetList::DEAD_CODE,
            SetList::PHP_82,
        ]
    );
};
