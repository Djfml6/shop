<?php

namespace App\Models;


class Address extends BaseModel
{
    protected $fillable = ['consignee', 'country', 'province', 'city', 'district', 'address_detail', 'mobile', 'house_number'];
    protected $casts = [
    	'is_default' => 'boolean'
    ];
}
