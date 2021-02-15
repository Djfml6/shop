<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends BaseModel
{
    public function goods(){
        return $this->hasOne("App\Models\Goods",'id','goods_id');
    }
}
