<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeeklyInsight extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'since_date',
        'until_date',
        'metrics',
    ];

    protected $casts = [
        'since_date' => 'date',
        'until_date' => 'date',
        // Otomatis mengubah JSON dari DB menjadi Array PHP saat ditarik
        'metrics'    => 'array', 
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
