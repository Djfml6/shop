<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends BaseModel
{
	protected $casts = [
		'store_slide' => 'json',
        'store_status' => 'boolean'
	];

    // 店铺评论关联
    public function comments(){
        return $this->hasMany(OrderComment::class,'store_id','id');
    }

    // 订单
    public function orders(){
        return $this->hasMany(Order::class,'store_id','id');
    }

}
