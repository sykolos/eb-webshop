<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminExport extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'filters' => 'array',
        'expires_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'rows_count' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isDownloadable(): bool
    {
        return $this->status === 'completed'
            && filled($this->file_path)
            && (! $this->expires_at || $this->expires_at->isFuture());
    }
}
