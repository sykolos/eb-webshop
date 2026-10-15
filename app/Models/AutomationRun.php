<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutomationRun extends Model
{
    protected $guarded = [];

    protected $casts = [
        'payload' => 'array',
        'scheduled_for' => 'datetime',
        'executed_at' => 'datetime',
    ];

    public function rule()
    {
        return $this->belongsTo(AutomationRule::class, 'rule_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
