<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Loan;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'loan_id',
        'amount',
        'type',
        'reference'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }
}
