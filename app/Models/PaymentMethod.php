<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',             // mobile_money, bank_transfer
        'provider',         // MTN, Airtel, Equity, etc.
        'account_number',   // phone number or bank account
        'account_name',
        'is_default',
        'is_verified',
    ];

    protected $casts = [
        'is_default'  => 'boolean',
        'is_verified' => 'boolean',
    ];

    // Belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}