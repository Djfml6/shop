<?php

namespace App\Http\Resources\Api\CouponResource;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CouponUserCollection extends ResourceCollection
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data'=>$this->collection->map(function($item){
                return [
                    'id'                        =>  $item->id,
                    'name'                      =>  $item->coupon->name,
                    'money'                     =>  $item->coupon->money,
                    'money_desc'                =>  $item->coupon->money_desc,
                    'store_name'                =>  $item->store->store_name,
                    'status'                    =>  $item->status,
                    'start_time'                =>  $item->coupon->start_time->format('Y-m-d H:i:s'),
                    'end_time'                  =>  $item->coupon->end_time,
                    'created_at'                =>  $item->coupon->created_at,
                    'type'                      =>  $item->coupon->type,
                    'range_type'                =>  $item->coupon->range_type,
                    'range_desc'                =>  $item->coupon->range_desc,
                ];
            }),
            'total'=>$this->total(), // 数据总数
            'per_page'=>$this->perPage(), // 每页数量
            'current_page'=>$this->currentPage(), // 当前页码
        ];
    }
}
