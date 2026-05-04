<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScrapingJob extends Model
{

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED_RETRYABLE = 'failed_retryable';
    const STATUS_FAILED_NON_RETRYABLE = 'failed_non_retryable';


    protected $fillable = [
        'account_id',
        'status',
        'attempt_count',
        'since_date',
        'until_date',
        'started_at',
        'finished_at',
        'last_error'
    ];

    protected $casts =[
        'since_date' => 'date',
        'until_date' => 'date',
        'started_at' => 'datetime',
        'finished_at' => 'datetime'
    ];


    public function account(){
        return $this->belongsTo(Account::class);
    }


    public function isPending(){
        return $this->status === self::STATUS_PENDING;
    }

    public function scopePending($query){
        return $query->where('status', self::STATUS_PENDING);
    }

    public function isProcessing(){
        return $this->status === self::STATUS_PROCESSING;
    }

    public function isFinished(){
        return in_array($this->status, [
            self::STATUS_SUCCESS,
            self::STATUS_FAILED_RETRYABLE,
            self::STATUS_FAILED_NON_RETRYABLE
        ]);
    }

    public function canRetry(){
        return $this->status === self::STATUS_FAILED_RETRYABLE;
    }

    public function hasReachMaxRetry($max = 3){
        return $this-> attempt_count >= $max;
    }
}
