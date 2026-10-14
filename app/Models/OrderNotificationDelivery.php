<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderNotificationDelivery extends Model
{
    protected $fillable = [
        'order_id',
        'notification_type',
        'status',
        'attempts',
        'sent_at',
        'last_error',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
