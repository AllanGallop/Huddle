<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProjectImage extends Model
{
    protected $fillable = [
        'project_id',
        'image_url',
        'disk',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function storageDisk(): string
    {
        if ($this->disk && Storage::disk($this->disk)->exists($this->image_url)) {
            return $this->disk;
        }

        if (Storage::disk('local')->exists($this->image_url)) {
            return 'local';
        }

        if (Storage::disk('public')->exists($this->image_url)) {
            return 'public';
        }

        return $this->disk ?: 'local';
    }

    public function url(): string
    {
        return route('projects.image', [
            'project' => $this->project_id,
            'projectImage' => $this->id,
        ]);
    }
}
