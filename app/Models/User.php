<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;
    protected $table = 'users';
    protected $guarded = [];
    protected $hidden = [
    	'password',
    	'pay_password'
    ];

    public function getJWTIdentifier(){
        return $this->getKey();
    }

    public function getJWTCustomClaims(){
        return [];
    }
    public function money()
    {
        return $this->hasMany(MoneyLog::class);
    }
    public function address()
    {
        return $this->hasMany(Address::class);
    }
    public function coupon_log()
    {
        return $this->hasMany(CouponLog::class);
    }
}
