<?php

namespace App\Services;

use App\Models\OrganizationSetting;
use App\Support\DefaultBrandingGenerator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class BrandingService
{
    public function ensureDefaultAssets(): void
    {
        $directory = public_path('images/branding');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        foreach (DefaultBrandingGenerator::assets() as $filename => $contents) {
            $path = $directory.'/'.$filename;

            if (! is_file($path)) {
                file_put_contents($path, $contents);
            }
        }
    }

    public function storeUpload(OrganizationSetting $settings, UploadedFile|TemporaryUploadedFile $file, string $field): string
    {
        $column = $this->columnForField($field);
        $this->removeAsset($settings->{$column});

        $path = $file->store('branding', 'public');

        $settings->update([$column => $path]);

        return $path;
    }

    public function resetBranding(OrganizationSetting $settings): void
    {
        foreach (['logo_path', 'favicon_path', 'banner_light_path', 'banner_dark_path'] as $column) {
            $this->removeAsset($settings->{$column});
        }

        $settings->update([
            'logo_path' => null,
            'favicon_path' => null,
            'banner_light_path' => null,
            'banner_dark_path' => null,
        ]);
    }

    public function removeAsset(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    protected function columnForField(string $field): string
    {
        return match ($field) {
            'logo' => 'logo_path',
            'favicon' => 'favicon_path',
            'banner_light' => 'banner_light_path',
            'banner_dark' => 'banner_dark_path',
            default => throw new \InvalidArgumentException("Unknown branding field [{$field}]."),
        };
    }
}
