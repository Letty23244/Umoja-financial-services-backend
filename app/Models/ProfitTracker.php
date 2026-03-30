<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProfitTracker extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_name',
        'revenue',
        'expenses',
        'profit',
        'category',
        'description',
        'record_date',
        'period',
    ];

    protected $casts = [
        'revenue'     => 'decimal:2',
        'expenses'    => 'decimal:2',
        'profit'      => 'decimal:2',
        'record_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Auto calculate profit before saving
    protected static function booted()
    {
        static::saving(function ($record) {
            $record->profit = $record->revenue - $record->expenses;
        });
    }
}