<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailDelivery extends Model
{
    protected $guarded = [];

    protected $casts = ['sent_at' => 'datetime'];

    public function campaign()
    {
        return $this->belongsTo(EmailCampaign::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
