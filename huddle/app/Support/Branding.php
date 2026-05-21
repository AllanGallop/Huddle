<?php

namespace App\Support;

class Branding
{
    public const PRIMARY = '#287878';

    public static function path(string $filename): string
    {
        return resource_path('images/branding/'.$filename);
    }

    public static function tintSvg(string $svg): string
    {
        return str_replace(
            ['fill="#000000"', "fill='#000000'", 'fill="#000"', "fill='#000'"],
            ['fill="'.self::PRIMARY.'"', "fill='".self::PRIMARY."'", 'fill="'.self::PRIMARY.'"', "fill='".self::PRIMARY."'"],
            $svg,
        );
    }

    public static function tintedDataUri(string $filename): string
    {
        $svg = self::tintSvg((string) file_get_contents(self::path($filename)));
        $mime = str_ends_with($filename, '.svg') ? 'image/svg+xml' : 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode($svg);
    }

    public static function bannerSrc(bool $forPdf = false): string
    {
        return self::tintedDataUri('MIS_Banner_Black.svg');
    }

    public static function logoUrl(): string
    {
        return asset('images/branding/logo_only.svg');
    }

    public static function bannerUrl(bool $forDarkBackground = false): string
    {
        return asset('images/branding/'.($forDarkBackground ? 'MIS_Banner_White.svg' : 'MIS_Banner_Black.svg'));
    }
}
