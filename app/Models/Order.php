<?php

namespace App\Models;

use App\Http\Constant;

class Order extends BaseModel
{
    protected $guarded = [];
    protected $dates = ['pay_time'];
    protected $appends = ['payment_name_cn', 'order_status_cn'];

    public function order_goods(){
        return $this->hasMany(OrderGoods::class,'order_id','id');
    }

    // 获取店铺信息
    public function store(){
        return $this->hasOne(Store::class,'id','store_id');
    }

    // 获取店铺信息
    public function user(){
        return $this->hasOne(User::class,'id','user_id');
    }

    // 获取售后
    public function refund(){
        return $this->hasOne(Refund::class,'order_id','id');
    }

    // 获取分销日志
    public function distribution(){
        return $this->hasMany(DistributionLog::class,'order_id','id');
    }

    // 获取支付状态 
    public function getPaymentNameCnAttribute()
    {
        $cn = '未支付';
        switch($this->payment_name){
            case 'wechat':
                $cn = __('admins.payment_wechat');
                break;
            case 'ali':
                $cn = __('admins.payment_ali');
                break;
            case 'money':
                $cn = __('admins.payment_money');
                break;
        }
        return $cn;
    }

    // 获取订单状态
    public function getOrderStatusCnAttribute()
    {
        $cn = '未知订单';
        switch($this->order_status){
            case Constant::ORDER_STATUS_CANCLE:
                $cn = __('admins.order_cancel');
                break;
            case Constant::ORDER_STATUS_WAITPAY:
                $cn = __('admins.wait_pay');
                break;
            case Constant::ORDER_STATUS_WAITREC:
                $cn = __('admins.wait_send');
                break;
            case Constant::ORDER_STATUS_CONFIRM:
                $cn = __('admins.order_confirm');
                break;
            case Constant::ORDER_STATUS_WAITCOMMENT:
                $cn = __('admins.wait_comment');
                break;
            case Constant::ORDER_STATUS_SERVICE:
                if($order_info['refund_type'] == 0){
                    $cn = __('admins.order_refund');
                }elseif($order_info['refund_type'] == 1){
                    $cn = __('admins.order_returned');
                }else{
                    $cn = __('admins.order_refund_over');
                }
                
                break;
            case Constant::ORDER_STATUS_COMPLETE:
                $cn = __('admins.order_completion');
                break;
        }
        return $cn;
    }
}
