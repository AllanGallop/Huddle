<?php

namespace App\Support;

use App\Models\OrganizationSetting;
use Illuminate\Support\Facades\Storage;

class Branding
{
    public const PRIMARY = '#287878';

    public static function path(string $filename): string
    {
        return public_path('images/branding/'.$filename);
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
        $path = self::path($filename);

        if (! is_file($path)) {
            $path = self::path('banner-light.svg');
        }

        $svg = self::tintSvg((string) file_get_contents($path));
        $mime = str_ends_with($filename, '.svg') ? 'image/svg+xml' : 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode($svg);
    }

    public static function bannerSrc(bool $forPdf = false): string
    {
        $settings = OrganizationSetting::instance();
        $path = $forPdf ? $settings->banner_light_path : $settings->banner_light_path;

        if ($path && Storage::disk('public')->exists($path)) {
            return self::dataUriFromStorage($path);
        }

        $default = self::path('banner-light.svg');

        if (! is_file($default)) {
            app(\App\Services\BrandingService::class)->ensureDefaultAssets();
        }

        $svg = (string) file_get_contents(self::path('banner-light.svg'));

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    public static function logoUrl(): string
    {
        return self::assetUrl(
            OrganizationSetting::instance()->logo_path,
            'images/branding/logo.svg',
        );
    }

    public static function faviconUrl(): string
    {
        $settings = OrganizationSetting::instance();

        if ($settings->favicon_path && Storage::disk('public')->exists($settings->favicon_path)) {
            return Storage::disk('public')->url($settings->favicon_path);
        }

        return self::logoUrl();
    }

    public static function appleTouchIconUrl(): string
    {
        $settings = OrganizationSetting::instance();
        $path = $settings->favicon_path ?? $settings->logo_path;

        if ($path && Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }

        return asset('images/branding/logo.svg');
    }

    public static function bannerUrl(bool $forDarkBackground = false): string
    {
        $settings = OrganizationSetting::instance();
        $path = $forDarkBackground ? $settings->banner_dark_path : $settings->banner_light_path;
        $default = $forDarkBackground ? 'images/branding/banner-dark.svg' : 'images/branding/banner-light.svg';

        return self::assetUrl($path, $default);
    }

    protected static function assetUrl(?string $customPath, string $defaultPublicPath): string
    {
        if ($customPath && Storage::disk('public')->exists($customPath)) {
            return Storage::disk('public')->url($customPath);
        }

        $full = public_path($defaultPublicPath);

        if (! is_file($full)) {
            app(\App\Services\BrandingService::class)->ensureDefaultAssets();
        }

        return asset($defaultPublicPath);
    }

    protected static function dataUriFromStorage(string $path): string
    {
        $contents = Storage::disk('public')->get($path);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        $mime = match ($extension) {
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            default => 'application/octet-stream',
        };

        return 'data:'.$mime.';base64,'.base64_encode($contents);
    }
}
