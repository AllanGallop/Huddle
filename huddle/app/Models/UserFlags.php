<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class UserFlags extends Model
{
    protected $table = 'user_flags';

    protected $fillable = [
        'name',
        'description',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_flag_assignments', 'user_flag_id', 'user_id')
            ->withTimestamps();
    }
}
