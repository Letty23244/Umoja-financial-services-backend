<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Debtor extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'amount_owed',
        'due_date'
    ];
}
