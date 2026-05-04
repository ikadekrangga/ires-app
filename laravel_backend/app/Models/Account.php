<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Account extends Model{
    protected $fillable = [
        'account_name',
        'instagram_business_id',
        'facebook_page_id',
        'access_token',
        'token_expires_at',

    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
    ];

    protected $hidden = [
        'access_token'
    ];

    public function scrapingJobs(){
        return $this->hasMany(ScrapingJob::class);
    }
    public function insights(){
        return $this->hasMany(WeeklyInsight::class);
    }

    public function isTokenExpired(){
        return $this -> token_expires_at
            && $this->token_expires_at ->isPast();
    }
}


