<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SavingTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'saving_wallet_id',
        'type',
        'amount',
        'reference',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function savingWallet()
    {
        return $this->belongsTo(SavingWallet::class);
    }
}