<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupLog extends BaseModel
{
    protected $guarded = [];
    public function goods(){
        return $this->hasOne("App\Models\Goods","id","goods_id");
    }

    public function user(){
        return $this->hasOne("App\Models\User","id","user_id");
    }

    public function orders(){
        return $this->hasMany('App\Models\Order','group_log_id','id'); // 这里是团日志ID 非团ID
    }
}
