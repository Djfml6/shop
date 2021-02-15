<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Goods extends BaseModel
{
    protected $table = 'goods';
    protected $casts = [
        'goods_status' => 'boolean',
        'goods_images' => 'json'
    ];
    protected $guarded = [];

    public function goods_cate(){
        return $this->hasOne(GoodsCate::class,'id','cate_id');
    }

    public function goods_brand(){
        return $this->hasOne(GoodsBrand::class,'id','brand_id');
    }

    public function goods_skus(){
        return $this->hasMany(GoodsSku::class,'goods_id','id');
    }

    public function goods_sku(){
        return $this->hasOne(GoodsSku::class,'goods_id','id');
    }

    public function store(){
        return $this->hasOne(Store::class,'id','store_id');
    }

    // 获取评论数量
    public function order_comment(){
        return $this->hasMany(OrderComment::class,'goods_id','id');
    }

    // 订单商品
    public function order_goods(){
        return $this->hasMany(OrderGoods::class,'goods_id','id');
    }

    // 获取分销ID
    public function distribution(){
        return $this->hasOne(Distribution::class,'goods_id','id');
    }

    // 获取秒杀
    public function seckill(){
        return $this->hasOne(Seckill::class,'goods_id','id');
    }

    // 获取拼团
    public function group(){
        return $this->hasOne(Group::class,'goods_id','id');
    }
}
