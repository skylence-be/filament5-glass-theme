<?php

namespace Skylence\GlassTheme;

use Skylence\GlassTheme\Icons\IconoirIcons;
use Filament\Panel;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;

class GlassTheme extends ThemeCore
{
    public string $identifier = 'skylence-glass-theme';

    public function colorConfiguration(Panel $panel): void
    {
        FilamentColor::register([
            'primary' => Color::Blue,
            'secondary' => Color::Indigo,
            'success' => Color::Lime,
            'warning' => Color::Yellow,
            'danger' => Color::Rose,
        ]);
    }

    public function fontConfiguration(Panel $panel): void
    {
        $panel->font('Manrope');
    }

    public function iconConfiguration(Panel $panel): void
    {
        $panel->plugin(IconoirIcons::make());
    }

    public function boot(Panel $panel): void
    {
        FilamentColor::addShades('badge', [100, 500]);
        FilamentColor::removeShades('badge', [50, 400, 600]);
        FilamentColor::addShades('button', [950]);
    }
}
