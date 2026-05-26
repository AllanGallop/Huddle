<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormSubmission extends Model
{
    protected $fillable = [
        'form_id',
        'user_id',
        'submitted_at',
        'score',
        'max_score',
        'passed',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'score' => 'integer',
            'max_score' => 'integer',
            'passed' => 'boolean',
        ];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(FormSubmissionAnswer::class);
    }

    public function scorePercentage(): ?int
    {
        if ($this->max_score === null || $this->max_score === 0 || $this->score === null) {
            return null;
        }

        return (int) round(($this->score / $this->max_score) * 100);
    }
}
