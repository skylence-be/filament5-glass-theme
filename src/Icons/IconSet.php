<?php

namespace Skylence\GlassTheme\Icons;

use BadMethodCallException;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Facades\FilamentIcon;
use InvalidArgumentException;

abstract class IconSet implements Plugin
{
    protected string $pluginId;
    protected mixed $iconEnum;
    protected string $iconPrefix;
    protected array $iconMap = [];
    protected array $aliasOverrides = [];
    protected array $iconOverrides = [];
    protected array $aliasStyleOverrides = [];
    protected array $iconStyleOverrides = [];
    protected mixed $styleEnum = null;
    protected mixed $currentStyle = null;

    public function getIconEnum(): mixed
    {
        return $this->iconEnum;
    }

    public function getStyleEnum(): ?string
    {
        return $this->styleEnum;
    }

    public function getAvailableStyles(): array
    {
        if (! $this->styleEnum) {
            return [];
        }

        return $this->styleEnum::cases();
    }

    public function getAvailableStyleNames(): array
    {
        if (! $this->styleEnum) {
            return [];
        }

        return $this->styleEnum::getStyleNames();
    }

    public function hasStyle(string|object $style): bool
    {
        if (! $this->styleEnum) {
            return false;
        }

        if (is_string($style)) {
            return $this->styleEnum::fromStyleName($style) !== null;
        }

        return in_array($style, $this->styleEnum::cases());
    }

    final public function registerIcons()
    {
        $icons = collect($this->iconMap)
            ->mapWithKeys(function ($iconCase, $alias) {
                $finalIconCase = null;

                if (isset($this->aliasOverrides[$alias])) {
                    $finalIconCase = $this->aliasOverrides[$alias];
                } elseif (isset($this->iconOverrides[$iconCase->value])) {
                    $finalIconCase = $this->iconOverrides[$iconCase->value];
                } elseif (isset($this->aliasStyleOverrides[$alias])) {
                    $finalIconCase = $this->findIconWithStyle($iconCase, $this->aliasStyleOverrides[$alias]);
                } elseif (isset($this->iconStyleOverrides[$iconCase->value])) {
                    $finalIconCase = $this->findIconWithStyle($iconCase, $this->iconStyleOverrides[$iconCase->value]);
                } else {
                    $finalIconCase = $this->applyStyleTransformation($iconCase);
                }

                $finalIconCase = $finalIconCase ?? $iconCase;

                $prefix = $this->iconPrefix ?? strtolower(class_basename($this->iconEnum));
                $iconString = $prefix.'-'.$finalIconCase->value;

                return [$alias => $iconString];
            })
            ->toArray();

        FilamentIcon::register($icons);
    }

    final public function overrideAlias(string $alias, mixed $iconCase): static
    {
        $this->aliasOverrides[$alias] = $iconCase;

        return $this;
    }

    final public function overrideAliases(array $overrides): static
    {
        foreach ($overrides as $alias => $iconCase) {
            $this->aliasOverrides[$alias] = $iconCase;
        }

        return $this;
    }

    final public function overrideIcon(mixed $fromIconCase, mixed $toIconCase): static
    {
        $this->iconOverrides[$fromIconCase->value] = $toIconCase;

        return $this;
    }

    final public function overrideIcons(array $overrides): static
    {
        foreach ($overrides as $fromIconCase => $toIconCase) {
            $fromKey = is_object($fromIconCase) ? $fromIconCase->value : $fromIconCase;
            $this->iconOverrides[$fromKey] = $toIconCase;
        }

        return $this;
    }

    final public function overrideStyleForAlias(string|array $aliases, string|object $style): static
    {
        if (! $this->hasStyle($style)) {
            $styleIdentifier = is_object($style) ? $style::class : $style;
            $availableStyleNames = $this->getAvailableStyleNames();

            throw new InvalidArgumentException("Style '{$styleIdentifier}' is not available. Available: ".implode(', ', $availableStyleNames));
        }

        $styleEnum = is_string($style) ? $this->styleEnum::fromStyleName($style) : $style;

        if (is_array($aliases)) {
            foreach ($aliases as $alias) {
                $this->aliasStyleOverrides[$alias] = $styleEnum;
            }
        } else {
            $this->aliasStyleOverrides[$aliases] = $styleEnum;
        }

        return $this;
    }

    final public function overrideStyleForIcon(mixed $iconCases, string|object $style): static
    {
        if (! $this->hasStyle($style)) {
            $styleIdentifier = is_object($style) ? $style::class : $style;
            $availableStyleNames = $this->getAvailableStyleNames();

            throw new InvalidArgumentException("Style '{$styleIdentifier}' is not available. Available: ".implode(', ', $availableStyleNames));
        }

        $styleEnum = is_string($style) ? $this->styleEnum::fromStyleName($style) : $style;

        if (is_array($iconCases)) {
            foreach ($iconCases as $iconCase) {
                $iconKey = is_object($iconCase) ? $iconCase->value : $iconCase;
                $this->iconStyleOverrides[$iconKey] = $styleEnum;
            }
        } else {
            $iconKey = is_object($iconCases) ? $iconCases->value : $iconCases;
            $this->iconStyleOverrides[$iconKey] = $styleEnum;
        }

        return $this;
    }

    private function applyStyleTransformation(mixed $iconCase): ?object
    {
        if (! $this->currentStyle) {
            return null;
        }

        return $this->findIconWithStyle($iconCase, $this->currentStyle);
    }

    private function findIconWithStyle(mixed $iconCase, mixed $targetStyle): ?object
    {
        if (! $this->hasStyle($targetStyle)) {
            return null;
        }

        $baseName = $this->extractBaseName($iconCase);
        $targetSuffix = $targetStyle->getEnumSuffix();

        if (str_ends_with($this->currentStyle->value, '-')) {
            $targetCaseName = $targetSuffix.$baseName;
        } elseif (str_starts_with($this->currentStyle->value, '-')) {
            $targetCaseName = $baseName.$targetSuffix;
        } else {
            $targetCaseName = $baseName.$targetSuffix;
        }

        $enumClass = $this->getIconEnum();

        foreach ($enumClass::cases() as $case) {
            if ($case->name === $targetCaseName) {
                return $case;
            }
        }

        return null;
    }

    private function extractBaseName(mixed $iconCase): string
    {
        $caseName = $iconCase->name;
        if (! $this->styleEnum) {
            return $caseName;
        }

        foreach ($this->styleEnum::cases() as $style) {
            $styleSuffix = $style->getEnumSuffix();

            if (str_ends_with($style->value, '-') && str_starts_with($caseName, $styleSuffix)) {
                return substr($caseName, strlen($styleSuffix));
            }

            if (str_ends_with($caseName, $styleSuffix)) {
                return substr($caseName, 0, -strlen($styleSuffix));
            }
        }

        return $caseName;
    }

    public function style(string $style): static
    {
        if (! $this->styleEnum) {
            throw new InvalidArgumentException('No style enum configured for this icon set.');
        }

        if (! $this->hasStyle($style)) {
            $availableStyleNames = $this->getAvailableStyleNames();

            throw new InvalidArgumentException("Style '{$style}' is not available. Available: ".implode(', ', $availableStyleNames));
        }

        $this->currentStyle = $this->styleEnum::fromStyleName($style);

        return $this;
    }

    public function __call(string $name, array $arguments): static
    {
        if ($this->hasStyle($name)) {
            return $this->style($name);
        }

        $availableStyleNames = $this->getAvailableStyleNames();

        throw new BadMethodCallException("Method '{$name}' does not exist. Available styles: ".implode(', ', $availableStyleNames));
    }

    final public function getId(): string
    {
        return $this->pluginId;
    }

    final public function boot(Panel $panel): void
    {
        static::registerIcons();
    }

    final public static function make(): static
    {
        return app(static::class);
    }

    public function register(Panel $panel): void
    {
        //
    }
}
