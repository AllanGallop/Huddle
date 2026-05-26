<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormSubmissionAnswer extends Model
{
    protected $fillable = [
        'form_submission_id',
        'form_question_id',
        'value',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'array',
        ];
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(FormSubmission::class, 'form_submission_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(FormQuestion::class, 'form_question_id');
    }
}
