<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Number;

class Project extends Model
{
    public const STATUSES = [
        'draft',
        'outstanding',
        'in-progress',
        'completed',
        'cancelled',
        'archived',
    ];

    public const FINANCIAL_STATUSES = [
        'quoted',
        'invoiced',
        'deposit_paid',
        'paid',
    ];

    protected $fillable = [
        'name',
        'description',
        'created_by',
        'leader_id',
        'volunteer_required',
        'project_status',
        'due_date',
        'financial_status',
        'quote_amount',
        'invoice_amount',
        'deposit_amount',
        'payment_amount',
        'quote_notes',
        'invoice_notes',
        'quoted_at',
        'invoiced_at',
        'deposit_paid_at',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'volunteer_required' => 'boolean',
            'due_date' => 'date',
            'quote_amount' => 'decimal:2',
            'invoice_amount' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'payment_amount' => 'decimal:2',
            'quoted_at' => 'datetime',
            'invoiced_at' => 'datetime',
            'deposit_paid_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ProjectComment::class);
    }

    public function topLevelComments(): HasMany
    {
        return $this->hasMany(ProjectComment::class)->whereNull('parent_comment_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProjectImage::class);
    }

    public function volunteers(): HasMany
    {
        return $this->hasMany(ProjectVolunteer::class);
    }

    public function statusLabel(): string
    {
        return str($this->project_status)->headline()->toString();
    }

    public function formattedId(int $length = 4): string
    {
        return str_pad((string) $this->id, $length, '0', STR_PAD_LEFT);
    }

    public function financialStatusLabel(): ?string
    {
        if (! $this->financial_status) {
            return null;
        }

        return str($this->financial_status)->headline()->toString();
    }

    public function balanceDue(): float
    {
        $invoice = (float) ($this->invoice_amount ?? 0);
        $deposit = (float) ($this->deposit_amount ?? 0);
        $payment = (float) ($this->payment_amount ?? 0);

        return max(0, round($invoice - $deposit - $payment, 2));
    }

    public function formatMoney(?float $amount): string
    {
        if ($amount === null) {
            return '—';
        }

        if (extension_loaded('intl')) {
            return Number::currency($amount, 'GBP');
        }

        return '£'.number_format($amount, 2);
    }

    public function isOverdue(): bool
    {
        if (! $this->due_date || in_array($this->project_status, ['completed', 'cancelled', 'archived'], true)) {
            return false;
        }

        return $this->due_date->isPast();
    }
}
