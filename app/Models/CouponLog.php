<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CouponLog extends BaseModel
{
    public function user(){
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function store(){
        return $this->hasOne(Store::class, 'id', 'store_id');
    }

    public function coupon()
    {
    	return $this->belongsTo(Coupon::class);
    }

}
