<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SavingsGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'savings_wallet_id',
        'name',             // e.g. "School Fees", "New Business"
        'target_amount',
        'current_amount',
        'target_date',
        'status',           // active, completed, cancelled
    ];

    protected $casts = [
        'target_amount'  => 'decimal:2',
        'current_amount' => 'decimal:2',
        'target_date'    => 'date',
    ];

    // Goal belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Goal is linked to a savings wallet
    public function savingsWallet()
    {
        return $this->belongsTo(SavingsWallet::class);
    }

    // Helper: get progress percentage
    public function getProgressPercentageAttribute(): float
    {
        if ($this->target_amount <= 0) return 0;
        return min(100, ($this->current_amount / $this->target_amount) * 100);
    }
}