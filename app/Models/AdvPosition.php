<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdvPosition extends BaseModel
{
    public function adv()
    {
    	return $this->hasMany(Adv::class, 'ap_id', 'id');
    }
}
