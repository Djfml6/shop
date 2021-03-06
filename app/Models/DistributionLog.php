<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DistributionLog extends BaseModel
{
    protected $guarded = [];

    public function user(){
        return $this->hasOne("App\Models\User",'id','user_id');
    }

    public function store(){
        return $this->hasOne("App\Models\Store",'id','store_id');
    }

    public function order(){
        return $this->hasOne("App\Models\Order",'id','order_id');
    }
}
