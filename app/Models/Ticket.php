<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $guarded = [];

    protected $casts = [
        'last_reply_at' => 'datetime',
        'last_customer_reply_at' => 'datetime',
        'last_admin_reply_at' => 'datetime',
        'response_due_at' => 'datetime',
        'resolution_due_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function messages()
    {
        return $this->hasMany(TicketMessage::class);
    }

    public function statusHistories()
    {
        return $this->hasMany(TicketStatusHistory::class)->latest('created_at');
    }

    public function reads()
    {
        return $this->hasMany(TicketRead::class);
    }
}
