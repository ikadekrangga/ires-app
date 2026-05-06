<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountCredential extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'access_token',
        'expires_at',
        'is_valid',
    ];

    // Cast data agar gampang dibaca
    protected $casts = [
        'expires_at' => 'datetime',
        'is_valid'   => 'boolean',
    ];

    // Jangan pernah nge-return access token di Response JSON publik!
    protected $hidden = [
        'access_token',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
