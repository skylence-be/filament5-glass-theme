# Glass Theme for Filament

A glassmorphism theme for Filament v4/v5 with Iconoir icons included.

## Installation

```bash
composer require skylence/glass-theme
```

## Usage

Add the theme to your Filament panel:

```php
use Skylence\GlassTheme\GlassTheme;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugin(GlassTheme::make());
}
```

## Configuration

You can customize which features the theme applies:

```php
GlassTheme::make()
    ->withSuggestedColors(false)  // Disable theme colors
    ->withSuggestedFont(false)    // Disable Manrope font
    ->withSuggestedIcons(false)   // Disable Iconoir icons
```

## Features

- **Glassmorphism Design** - Transparent backgrounds with backdrop blur effects
- **Iconoir Icons** - 1680+ icons mapped to Filament components
- **Custom Colors** - Curated color palette (Blue primary, Indigo secondary, Lime success, Yellow warning, Rose danger)
- **Manrope Font** - Clean, modern typography

## Building CSS

This theme uses Tailwind CSS. To build the theme CSS:

```bash
npm install
npm run build
```

## License

MIT
