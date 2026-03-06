<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    // Fields allowed for mass assignment
    protected $fillable = [
        'user_id',
        'amount',
        'description', // optional if you want a description for each deposit
    ];

    // Define relationship to User
    public function user()
    {
        return $this->belongsTo(User::class);
    }  //
}