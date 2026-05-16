<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'product_name',
        'quantity',
        'price',
        'total_amount',
        'sale_date',
    ];
}