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
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function storageDisk(): string
    {
        if (Storage::disk('local')->exists($this->image_url)) {
            return 'local';
        }

        return 'public';
    }

    public function url(): string
    {
        return route('projects.image', [
            'project' => $this->project_id,
            'projectImage' => $this->id,
        ]);
    }
}
