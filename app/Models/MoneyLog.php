<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MoneyLog extends BaseModel
{
    protected $fillable = [
		'user_id',
		'money',
		'is_type',
		'name',
		'info',   
    ];
}
