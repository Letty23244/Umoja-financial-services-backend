<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\SavingTransaction;

class SavingWallet extends Model
{
    protected $table = 'savings_wallets'; // matches your table

    protected $fillable = [
        'user_id',
        'balance',
        'status'
    ];

    // Relationship to User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship to transactions
    public function transactions()
    {
        return $this->hasMany(SavingTransaction::class, 'saving_wallet_id');
    }
}