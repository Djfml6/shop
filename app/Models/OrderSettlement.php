<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderSettlement extends BaseModel
{
    public function order(){
        return $this->hasOne('App\Models\Order','id','order_id');
    }
}
