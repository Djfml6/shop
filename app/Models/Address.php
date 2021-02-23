<?php

namespace App\Models;


class Address extends BaseModel
{
    protected $fillable = ['consignee', 'country_id', 'province_id', 'city_id', 'district_id', 'address_detail', 'mobile', 'area_info', 'is_default'];
    protected $casts = [
    	'is_default' => 'boolean'
    ];
    public function province()
    {
    	return $this->hasOne(Area::class, 'areaCode', 'province_id')->select(\DB::raw('areaCode,areaName'));
    }
    public function city()
    {
    	return $this->hasOne(Area::class, 'areaCode', 'city_id')->select(\DB::raw('areaCode,areaName'));
    }
    public function district()
    {
    	return $this->hasOne(Area::class, 'areaCode', 'district_id')->select(\DB::raw('areaCode,areaName'));
    }
}
