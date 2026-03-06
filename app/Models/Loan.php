<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
     protected $fillable = [
        'user_id',
        'amount',
        'interest_rate',
        'duration_months',
        'remaining_balance',
        'status'
    ];

    // Loan belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
//
}
