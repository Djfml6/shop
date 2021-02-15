<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMsg extends BaseModel
{
    protected $guarded = [];
    public function store(){
        return $this->hasOne('App\Models\Store','id','store_id');
    }
    public function user(){
        return $this->hasOne('App\Models\User','id','user_id');
    }
}
