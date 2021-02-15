<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends BaseModel
{
    public function permission_group(){
        return $this->hasOne('App\Models\PermissionGroup','id','pid');
    }
}
