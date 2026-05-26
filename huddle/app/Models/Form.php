<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Form extends Model
{
    public const TYPE_SURVEY = 'survey';

    public const TYPE_EXAM = 'exam';

    protected $fillable = [
        'title',
        'description',
        'type',
        'is_published',
        'pass_percentage',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'pass_percentage' => 'integer',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(FormQuestion::class)->orderBy('sort_order');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class);
    }

    public function isExam(): bool
    {
        return $this->type === self::TYPE_EXAM;
    }

    public function isSurvey(): bool
    {
        return $this->type === self::TYPE_SURVEY;
    }

    public function canManage(User $user): bool
    {
        return $user->canManageForms();
    }

    public function canTake(User $user): bool
    {
        if (! $this->is_published) {
            return $this->canManage($user);
        }

        return true;
    }

    public function maxPoints(): int
    {
        return (int) $this->questions->sum('points');
    }
}
