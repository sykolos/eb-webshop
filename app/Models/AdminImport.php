<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminImport extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'summary' => 'array',
        'applied_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
