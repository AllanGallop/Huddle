<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Accreditation extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(AccreditationAssignment::class);
    }

    public function mentors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'accreditation_mentors')
            ->withTimestamps()
            ->orderBy('name');
    }

    public function forms(): HasMany
    {
        return $this->hasMany(Form::class);
    }
}
