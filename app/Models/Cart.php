<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends BaseModel
{
    public function goods(){
        return $this->hasOne(Goods::class,'id','goods_id');
    }

    public function goods_sku(){
        return $this->hasOne(GoodsSku::class,'id','sku_id');
    }

    public function store(){
        return $this->hasOne(Store::class,'id','store_id');
    }

    public function carts(){
        return $this->hasMany(Cart::class,'store_id','store_id');
    }
}
