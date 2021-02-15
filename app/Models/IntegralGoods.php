<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegralGoods extends BaseModel
{
    protected $guarded = [];
    protected $casts = [
    	'goods_status' => 'boolean',
    	'is_recommend' => 'boolean',
    	'goods_images' => 'json'
    ];

}
