<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Home\IntegralOrderResource\IntegralOrderCollection;
use App\Http\Resources\Home\IntegralOrderResource\IntegralOrderResource;
use App\Models\IntegralGoods;
use App\Models\IntegralGoodsClass;
use App\Services\IntegralGoodsService;
use App\Services\IntegralOrderService;
use App\Traits\HelperTrait;
use Illuminate\Http\Request;
use App\Http\CodeResponse;

class IntegralController extends Controller
{
    use HelperTrait;

    // 获取订单列表
    public function get_orders(){
        $order_service = new IntegralOrderService();
        $list = $order_service->getOrders()['data'];
        return $this->success(new IntegralOrderCollection($list));
    }

    // 获取订单详情
    public function get_order_info($id){
        $order_service = new IntegralOrderService();
        $rs = $order_service->getOrderInfoById($id,'user');
        return $rs['status']?$this->success(new IntegralOrderResource($rs['data']),$rs['msg']):$this->error($rs['msg']);
    }

    public function index(IntegralGoods $ig_model,IntegralGoodsClass $igc_model){
        $data['recommend'] = $ig_model->where('goods_status', true)->where('is_recommend', true)->take(4)->get();
        $data['list'] = $igc_model->with(['integral_goods'=>function($q){
            $q->where('goods_status', true)->take(4);
        }])->get();
        return $this->success($data);
    }

    public function show(IntegralGoods $ig_model,$id){
        $info = $ig_model->where('goods_status', true)->where('id',$id)->first();
        if(empty($info))
        {
            return $this->fail(CodeResponse::GOODS_INVALID);
        }
        return $this->success($info);
    }

    // 搜索
    public function search(IntegralGoodsService $igs){
        $info = $igs->search();
        return $this->success($info);
    }

    // 获取积分分类
    public function get_integral_class(){
        return $this->success(IntegralGoodsClass::query()->get());
    }

    // 支付积分订单
    public function pay(IntegralOrderService $ios){
        $rs = $ios->createOrder();
        return $rs['status']?$this->success($rs['data']):$this->error($rs['msg']);
    }
}
