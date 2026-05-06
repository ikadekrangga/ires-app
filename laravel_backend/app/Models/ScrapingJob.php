<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScrapingJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'status',
        'since_date',
        'until_date',
        'attempt_count',
        'max_attempts',
        'next_retry_at',
        'error_reason',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'since_date'    => 'date',
        'until_date'    => 'date',
        'next_retry_at' => 'datetime',
        'started_at'    => 'datetime',
        'finished_at'   => 'datetime',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
