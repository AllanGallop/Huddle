<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormQuestionOption extends Model
{
    protected $fillable = [
        'form_question_id',
        'sort_order',
        'label',
        'is_correct',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_correct' => 'boolean',
        ];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(FormQuestion::class, 'form_question_id');
    }
}
