<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WikiPage extends Model
{
    protected $fillable = [
        'wiki_directory_id',
        'title',
        'slug',
        'created_by',
        'updated_by',
    ];

    public function directory(): BelongsTo
    {
        return $this->belongsTo(WikiDirectory::class, 'wiki_directory_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(WikiPageVersion::class)->orderByDesc('version_number');
    }

    public function latestVersion(): HasOne
    {
        return $this->hasOne(WikiPageVersion::class)->latestOfMany('version_number');
    }

    public function fullPath(): string
    {
        $prefix = $this->directory?->fullPath();

        return $prefix ? "{$prefix}/{$this->slug}" : $this->slug;
    }

    public function url(): string
    {
        return route('wiki.show', $this->fullPath());
    }
}
