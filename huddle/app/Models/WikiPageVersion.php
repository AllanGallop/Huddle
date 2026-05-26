<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WikiPageVersion extends Model
{
    protected $fillable = [
        'wiki_page_id',
        'version_number',
        'title',
        'body',
        'change_summary',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'version_number' => 'integer',
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(WikiPage::class, 'wiki_page_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
