<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_system',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user')
            ->withTimestamps();
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permission')
            ->withTimestamps();
    }

    public function hasPermission(string $slug): bool
    {
        if ($this->relationLoaded('permissions')) {
            return $this->permissions->contains(
                fn (Permission $permission): bool => $permission->slug === $slug,
            );
        }

        return $this->permissions()->where('slug', $slug)->exists();
    }
}
