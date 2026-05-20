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
}
