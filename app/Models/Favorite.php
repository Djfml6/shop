<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favorite extends BaseModel
{
    public function goods(){
        return $this->hasOne(Goods::class,'id','out_id');
    }

    public function store(){
        return $this->hasOne(Store::class,'id','out_id');
    }
}
