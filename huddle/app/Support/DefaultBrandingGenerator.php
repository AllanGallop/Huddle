<?php

namespace App\Support;

class DefaultBrandingGenerator
{
    public const PRIMARY = Branding::PRIMARY;

    /**
     * @return array<string, string>
     */
    public static function assets(): array
    {
        return [
            'logo.svg' => self::logoSvg(),
            'favicon.svg' => self::logoSvg(),
            'banner-light.svg' => self::bannerSvg(forDarkBackground: false),
            'banner-dark.svg' => self::bannerSvg(forDarkBackground: true),
        ];
    }

    public static function logoSvg(): string
    {
        $primary = self::PRIMARY;

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" role="img" aria-label="Huddle">
  <rect width="64" height="64" rx="14" fill="{$primary}"/>
  <g fill="#ffffff">
    <circle cx="32" cy="24" r="9"/>
    <circle cx="20" cy="42" r="8"/>
    <circle cx="44" cy="42" r="8"/>
  </g>
</svg>
SVG;
    }

    public static function bannerSvg(bool $forDarkBackground = false): string
    {
        $primary = self::PRIMARY;
        $textColor = $forDarkBackground ? '#ffffff' : '#18181b';

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 56" role="img" aria-label="Huddle">
  <rect x="4" y="4" width="48" height="48" rx="10" fill="{$primary}"/>
  <circle cx="28" cy="20" r="7" fill="#ffffff"/>
  <circle cx="18" cy="36" r="6" fill="#ffffff"/>
  <circle cx="38" cy="36" r="6" fill="#ffffff"/>
  <text x="64" y="38" font-family="system-ui, -apple-system, sans-serif" font-size="28" font-weight="600" fill="{$textColor}">Huddle</text>
</svg>
SVG;
    }
}
