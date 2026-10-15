<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketMessage extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = ['is_internal' => 'boolean', 'created_at' => 'datetime'];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    public function attachments()
    {
        return $this->hasMany(TicketAttachment::class);
    }
}
