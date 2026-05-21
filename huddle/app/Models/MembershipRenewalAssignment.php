<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MembershipRenewalAssignment extends Model
{
    protected $fillable = [
        'user_id',
        'membership_renewal_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function membershipRenewal(): BelongsTo
    {
        return $this->belongsTo(MembershipRenewal::class);
    }
}
