<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WikiDirectory extends Model
{
    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('name');
    }

    public function pages(): HasMany
    {
        return $this->hasMany(WikiPage::class)->orderBy('title');
    }

    public function fullPath(): string
    {
        $segments = [$this->slug];
        $parent = $this->parent;

        while ($parent) {
            array_unshift($segments, $parent->slug);
            $parent = $parent->parent;
        }

        return implode('/', $segments);
    }

    public function url(): string
    {
        return route('wiki.show', $this->fullPath());
    }
}
