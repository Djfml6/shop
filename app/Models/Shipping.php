<?php

namespace App\Models;


class Shipping extends BaseModel
{
    public function area()
    {
    	return $this->hasMany(ShippingArea::class);
    }
    public function free()
    {
    	return $this->hasMany(ShippingFree::class);
    }
}
