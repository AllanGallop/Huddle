<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MembershipRenewal extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(MembershipRenewalAssignment::class);
    }

    public function isCurrent(): bool
    {
        return $this->end_date->gte(now()->startOfDay());
    }
}
