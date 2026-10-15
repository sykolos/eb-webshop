<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketRead extends Model
{
    protected $guarded = [];

    protected $casts = ['last_read_at' => 'datetime'];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}
