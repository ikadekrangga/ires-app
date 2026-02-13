<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Account extends Model{
    protected $fillable = [
        'account_name',
        'ig_pageId',
        'fb_pageId',
        'access_token',

    ];
    public function insights(){
    return $this->hasMany(Insight::class);
}
}


