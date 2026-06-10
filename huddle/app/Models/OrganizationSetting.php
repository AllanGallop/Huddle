<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizationSetting extends Model
{
    protected $fillable = [
        'account_name',
        'bank_name',
        'sort_code',
        'account_number',
        'iban',
        'payment_instructions',
        'logo_path',
        'favicon_path',
        'banner_light_path',
        'banner_dark_path',
    ];

    public static function instance(): self
    {
        return static::query()->firstOrCreate([]);
    }

    public function hasBankDetails(): bool
    {
        return $this->account_name
            || $this->bank_name
            || $this->sort_code
            || $this->account_number
            || $this->iban;
    }

    public function hasCustomBranding(): bool
    {
        return $this->logo_path
            || $this->favicon_path
            || $this->banner_light_path
            || $this->banner_dark_path;
    }
}
