<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SupportTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subject',
        'category',     // loan, savings, account, general
        'status',       // open, in_progress, resolved, closed
        'priority',     // low, medium, high
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    // Ticket belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Ticket has many messages
    public function messages()
    {
        return $this->hasMany(SupportMessage::class);
    }

    // Latest message
    public function latestMessage()
    {
        return $this->hasOne(SupportMessage::class)->latestOfMany();
    }
}


class SupportMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'support_ticket_id',
        'sender_id',        // user or admin id
        'sender_type',      // customer, admin
        'message',
        'attachment_path',
    ];

    // Message belongs to a ticket
    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class);
    }

    // Message sender
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}