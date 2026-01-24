<?php

namespace Skylence\GlassTheme\Icons\Contracts;

/**
 * Interface that style enums must implement to be used with IconSet.
 */
interface StyleEnum
{
    public function getStyleName(): string;

    public function getEnumSuffix(): string;

    public static function getStyleNames(): array;

    public static function fromStyleName(string $styleName): mixed;

    public static function cases(): array;
}
