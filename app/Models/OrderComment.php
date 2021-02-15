<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderComment extends BaseModel
{
    // 获取店铺信息
    public function goods(){
        return $this->hasOne(Goods::class,'id','goods_id');
    }

    // 获取用户信息
    public function user(){
        return $this->hasOne(User::class,'id','user_id');
    }
}
