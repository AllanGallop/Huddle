<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormQuestion extends Model
{
    public const TYPE_YES_NO = 'yes_no';

    public const TYPE_MULTIPLE_CHOICE = 'multiple_choice';

    protected $fillable = [
        'form_id',
        'sort_order',
        'type',
        'body',
        'points',
        'correct_yes_no',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'points' => 'integer',
            'correct_yes_no' => 'boolean',
        ];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(FormQuestionOption::class)->orderBy('sort_order');
    }

    public function isYesNo(): bool
    {
        return $this->type === self::TYPE_YES_NO;
    }

    public function isMultipleChoice(): bool
    {
        return $this->type === self::TYPE_MULTIPLE_CHOICE;
    }
}
