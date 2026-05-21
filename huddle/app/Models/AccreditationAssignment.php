<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccreditationAssignment extends Model
{
    protected $fillable = [
        'user_id',
        'accreditation_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function accreditation(): BelongsTo
    {
        return $this->belongsTo(Accreditation::class);
    }
}
