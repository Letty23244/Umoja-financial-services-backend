<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AutoSavings extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'saving_wallet_id',
        'name',
        'amount',
        'frequency',
        'next_deduction_date',
        'payment_method',
        'payment_reference',
        'status',
    ];

    protected $casts = [
        'amount'               => 'decimal:2',
        'next_deduction_date'  => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function savingWallet()
    {
        return $this->belongsTo(SavingWallet::class);
    }

    // Calculate next deduction date based on frequency
    public function calculateNextDeductionDate(): string
    {
        return match ($this->frequency) {
            'daily'   => now()->addDay()->toDateString(),
            'weekly'  => now()->addWeek()->toDateString(),
            'monthly' => now()->addMonth()->toDateString(),
        };
    }
}