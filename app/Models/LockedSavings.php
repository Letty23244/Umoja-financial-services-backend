<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LockedSavings extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        // 'saving_wallet_id' ← REMOVED, column was dropped in migration
        'name',
        'amount',
        'interest_rate',
        'lock_duration_years',
        'locked_until',
        'status',
        'withdrawn_at',
    ];

    protected $casts = [
        'amount'             => 'decimal:2',
        'interest_rate'      => 'decimal:2',
        'lock_duration_years'=> 'integer',
        'locked_until'       => 'date',
        'withdrawn_at'       => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // savingWallet() removed — no foreign key on this table anymore

    // ── Computed properties ────────────────────────────────────

    public function hasMatured(): bool
    {
        return now()->greaterThanOrEqualTo($this->locked_until);
    }

    public function getInterestEarnedAttribute(): float
    {
        return round(
            $this->amount * ($this->interest_rate / 100) * $this->lock_duration_years,
            2
        );
    }

    public function getMaturityAmountAttribute(): float
    {
        return round($this->amount + $this->interest_earned, 2);
    }
}