<?php
namespace App\Services;

use App\Http\Resources\Home\CouponResource\CouponCollection;
use App\Models\Coupon;
use App\Models\CouponLog;
use Illuminate\Support\Facades\DB;
use App\Http\CodeResponse;
use App\Http\Constant;
use App\Exceptions\RequestException;
use Carbon\Carbon;

class CouponService extends BaseService{
    
    // 用户领取优惠券
    public function receive($id){
        $user_service = new UserService();
        $user_info = $user_service->getUserInfo();
        $coupon_id = $id;

        try{
            DB::beginTransaction();
            $coupon_model = new Coupon();
            $coupon_model = $coupon_model->find($coupon_id);
            if(!$coupon_model)
            {
                throw new RequestException(CodeResponse::COUPON_INVALID);
            }

            // 判断优惠券是否失效
            if($coupon_model->use_time_type == Constant::COUPON_USE_TIME_TYPE_BETWEEN)
            {
                if(now()->gt($coupon_model->end_time))
                {
                    throw new RequestException(CodeResponse::COUPON_INVALID, '优惠券已过期');
                }   
            }else{
                if($coupon_model->use_time_type == Constant::COUPON_USE_TIME_TYPE_NEXTEXPIRES)
                {
                    $coupon_model->start_time = Carbon::tomorrow();
                }
                $coupon_model->end_time = Carbon::parse("+ {$coupon_model->expires} days");
            }

            // 判断是否已发完
            if($coupon_model->stock <= 0 || $coupon_model->status == Constant::COUPON_STATUS_END)
            {
                throw new RequestException(CodeResponse::COUPON_INVALID, '优惠券已领完');
            }

            // 判断是否存在用户限制
            if($coupon_model->user_limit != Constant::COUPON_USER_NOTLIMIT && !in_array($user_info->level_id, explode(',', $coupon_model->user_limit)))
            {
                throw new RequestException(CodeResponse::COUPON_INVALID, '用户等级受限');
            }

            // 判断是否超过领取数量
            if($user_info->coupon_log()->where('coupon_id', $coupon_id)->count() >= $coupon_model->count)
            {
                throw new RequestException(CodeResponse::COUPON_INVALID, "每人限领取{$coupon_model->count}张");
            }

            $coupon_log_model = new CouponLog();
            $coupon_log_model->user_id = $user_info['id'];
            $coupon_log_model->coupon_id = $coupon_id;
            $coupon_log_model->store_id = $coupon_model->store_id;
            $coupon_log_model->start_time = $coupon_model->start_time;
            $coupon_log_model->end_time = $coupon_model->end_time;
            $coupon_log_model->get_type = Constant::COUPON_GET_ACTIVE;
            $coupon_log_model->save();

            $coupon_model->stock -= 1; // 库存减少 
            $coupon_model->get_num += 1;
            $coupon_model->save();
            DB::commit();
            return []; 
        }catch(\Exception $e){
            DB::rollBack();
            throw new RequestException(CodeResponse::COUPON_INVALID, $e->getMessage());
        }
    }

    /**
     * 使用优惠券 function
     *
     * @param integer $coupon_log_id // 领取到的优惠券
     * @param integer $order_id // 使用的订单
     * @return void
     * @Description
     * @author hg <www.qingwuit.com>
     */
    public function use_coupon($coupon_log_id=0,$order_id=0){
        if(empty($coupon_log_id)){
            return $this->format_error('markets.coupon_error');
        } 
        $coupon_log_model = new CouponLog();
        $coupon_log_model = $coupon_log_model->find($coupon_log_id);
        $coupon_log_model->order_id = $order_id;
        $coupon_log_model->status = 1;
        $coupon_log_model->save();
        return $this->format([]);
    }

    // 根据店铺ID 获取优惠券 列表
    public function getCouponByStoreId($store_id)
    {
        $list = Coupon::query()->where('store_id',$store_id)->where('stock','>',0)->where('start_time','<',now())->where('end_time','>',now())->get();

        if($list->isEmpty()){
            return [];
        }

        return $list;
    }

    // 获取所有可领取的优惠券列表
    public function getCoupon($field = '*')
    {
        $info = Coupon::query()->select(DB::raw($field))->where('status', Constant::COUPON_STATUS_NORMAL)->with(['store' => function($query){
             $query->select('id', 'store_name');
        }])->get();
        return $info;
    }
}
