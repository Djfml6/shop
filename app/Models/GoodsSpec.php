<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsSpec extends BaseModel
{
    protected $table = 'goods_specs';
    protected $fillable = ['name','attr_id','store_id'];
    public function attr()
    {
    	return $this->hasOne(GoodsAttr::class, 'id', 'attr_id');
    }
    public function self()
    {
    	return $this->hasMany(GoodsSpec::class, 'attr_id', 'attr_id');
    }
}
