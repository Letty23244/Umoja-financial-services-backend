<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LockedSavings extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'saving_wallet_id',  // ← back in, same as AutoSavings
        'name',
        'amount',
        'interest_rate',
        'lock_duration_years',
        'locked_until',
        'status',
        'withdrawn_at',
    ];

    protected $casts = [
        'amount'        => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'locked_until'  => 'date',
        'withdrawn_at'  => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function savingWallet()
    {
        return $this->belongsTo(SavingWallet::class);
    }

    public function hasMatured(): bool
    {
        return now()->greaterThanOrEqualTo($this->locked_until);
    }

    public function getInterestEarnedAttribute(): float
    {
        return $this->amount * ($this->interest_rate / 100) * $this->lock_duration_years;
    }

    public function getMaturityAmountAttribute(): float
    {
        return $this->amount + $this->interest_earned;
    }
}