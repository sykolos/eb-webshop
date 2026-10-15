<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerRegistrationRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'reviewed_by', 'name', 'email', 'company_name',
        'vatnumber', 'phone', 'message', 'status', 'review_note', 'reviewed_at',
    ];

    protected $casts = ['reviewed_at' => 'datetime'];

    public const STATUSES = ['pending', 'approved', 'rejected', 'blocked'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
