<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'order_updates', 'newsletter_subscribed', 'marketing_notifications'];

    protected $casts = [
        'order_updates' => 'boolean',
        'newsletter_subscribed' => 'boolean',
        'marketing_notifications' => 'boolean',
    ];
}
