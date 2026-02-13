<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Insight extends Model
{
    use HasFactory;

    // TAMBAHKAN INI 👇
    protected $fillable = [
        'account_id',    // Penting untuk relasi
        'reach',
        'impressions',
        'profile_views',
        'report_date'
    ];

    // Pastikan relasi balik ke Account ada (Opsional tapi bagus)
    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}