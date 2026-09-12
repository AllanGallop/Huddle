<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    public const TYPES = [
        'commercial',
        'domestic',
    ];

    protected $fillable = [
        'name',
        'address',
        'email',
        'telephone',
        'type',
    ];

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function typeLabel(): string
    {
        return str($this->type)->headline()->toString();
    }
}
