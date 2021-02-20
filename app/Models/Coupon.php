<?php

namespace App\Models;
use App\Http\Constant;

use Illuminate\Database\Eloquent\Model;

class Coupon extends BaseModel
{
    protected $dates = ['start_time','end_time'];
    protected $appends = ['money_desc', 'range_desc'];
    protected $casts = [
        'money' => 'float'
    ];

    public function store()
    {
    	return $this->hasOne(Store::class, 'id', 'store_id');
    }

    public function getMoneyDescAttribute()
    {
    	switch ($this->type) {
    		case Constant::COUPON_TYPE_FIXED:
    			return $this->money.'元';
    			break;
    		case Constant::COUPON_TYPE_PERCENT:
    			// return '打'.($this->money/10).'折,最多优惠'.$this->discount_to_many.'元';
                return ($this->money/10).'折';
    			break;
    		case Constant::COUPON_TYPE_SILL:
    			return $this->money.'元';
    			break;    			    		
    		default:
    			# code...
    			break;
    	}
    }

    public function getRangeDescAttribute()
    {
    	switch ($this->range_type) {
    		case Constant::COUPON_RANGE_ALL:
    			return '全场商品';
    			break;
    		case Constant::COUPON_RANGE_PART:
    			return '部分商品';
    			break;    		
    		default:
    			# code...
    			break;
    	}
    }
}
