<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'ig_account_id',
        'fb_page_id',
        'status',
    ];

    // Relasi untuk melihat semua history token
    public function credentials()
    {
        return $this->hasMany(AccountCredential::class);
    }

    // Helper untuk mengambil SATU token yang masih valid saat ini (digunakan Worker)
    public function activeCredential()
    {
        return $this->hasOne(AccountCredential::class)
            ->where('is_valid', true)
            ->latest('id'); 
    }

    public function jobs()
    {
        return $this->hasMany(ScrapingJob::class);
    }

    public function insights()
    {
        return $this->hasMany(WeeklyInsight::class);
    }
}
