<?php

namespace App\Http\Resources\Api\OrderResource;

use App\Services\KuaibaoService;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $kb_service = new KuaibaoService();
        return [
            'id'                    =>  $this->id,
            'order_no'              =>  $this->order_no,
            'consignee'             =>  $this->consignee,
            'mobile'                =>  $this->mobile,
            'area'                  =>  $this->area,
            'address_detail'        =>  $this->address_detail,
            'payment_name'          =>  $this->payment_name,
            'payment_name_cn'       =>  $this->payment_name_cn,
            'delivery_no'           =>  $this->delivery_no,
            'delivery_code'         =>  $this->delivery_code,
            'total_price'           =>  $this->total_price,
            'freight_money'         =>  $this->freight_money,
            'remark'                =>  $this->remark,
            'pay_time'              =>  $this->pay_time,
            'pay_price'             =>  $this->pay_price,
            'created_at'            =>  $this->created_at->format('Y-m-d H:i:s'),
            'order_status'          =>  $this->order_status,
            'order_status_cn'       =>  $this->order_status_cn,
            'order_price'           =>  $this->order_price,
            'coupon_money'          =>  $this->coupon_money,
            'delivery_list'         =>  empty($this->delivery_no)?[]:$kb_service->getExpressInfo($this->delivery_no, $this->delivery_code, $this->mobile),
            'order_goods'           =>  $this->order_goods->map(function($q){
                                        return [
                                            'goods_id'    => $q->goods_id,
                                            'goods_image' => $q->goods_image,
                                            'goods_name'  => $q->goods_name,
                                            'goods_price' => $q->goods_price,
                                            'sku_name'    => $q->sku_name,
                                            'buy_num'     => $q->buy_num,
                                        ];
            }),
            'store_name'            =>  $this->store->store_name
        ];
    }
}
