<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegralGoodsClass extends BaseModel
{
    public function integral_goods(){
        return $this->hasMany(IntegralGoods::class,'cid','id');
    }
}
