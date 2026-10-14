<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchedulerHeartbeat extends Model
{
    protected $fillable = ['name', 'environment', 'ran_at', 'meta'];

    protected $casts = [
        'ran_at' => 'datetime',
        'meta' => 'array',
    ];
}
