<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Creditor extends Model
{
     protected $fillable = [
        'name',
        'phone',
        'amount_due',
        'payment_date'
    ];
}
