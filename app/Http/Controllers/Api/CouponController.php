<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CouponResource\CouponUserCollection;
use App\Models\CouponLog;
use App\Services\CouponService;
use App\Services\UserService;
use Illuminate\Http\Request;
use App\Http\Requests\Api\CouponRequest;
use App\Http\Constant;

class CouponController extends Controller
{
    // 获取自己优惠券的列表
    public function my(CouponLog $coupon_log_model,UserService $user_service){
        $user_info = $user_service->getUserInfo();
        $info = $coupon_log_model->with('store', 'coupon')->where('status', Constant::COUPON_STATUS_NOT_USED)->where('user_id', $user_info['id'])->orderBy('id','desc')->paginate(request()->per_page ?? 30);
        return $this->success(new CouponUserCollection($info));
    }

    // 领取优惠券
    public function receive_coupon(CouponRequest $request, CouponService $coupon_service){
        $id = $request->id;
        $info = $coupon_service->receive($id);
        return $this->success();
    }

    // 商城的优惠券列表
    public function index(CouponService $coupon_service)
    {
        $info = $coupon_service->getCoupon();
        return $this->success($info);
    }
}
