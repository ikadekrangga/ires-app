<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeeklyInsight extends Model
{
    protected $fillable = [
        'account_id',
        'since_date',
        'until_date',
        'reach',
        'views',
        'likes',
        'comments',
        'follows_and_unfollows',
        'source'
    ];

    protected $casts = [
        'since_date' => 'date',
        'until_date' => 'date'
    ];

    public function account(){
        return $this->belongsTo(Account::class);
    }
        
    
}
