<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    
    // 获取订单列表
    public function get_orders(OrderService $order_service){
        $list = $order_service->getOrders();
        return $this->success($list);
    }

    // 创建订单
    public function create_order(OrderService $order_service){
        $info = $order_service->createOrder();
        return $this->success($info);
    }

    // // 创建订单前
    public function create_order_before(OrderService $order_service){
        $info = $order_service->createOrderBefore();
        return $this->success($info);
    }


    // 支付订单
    public function pay(OrderService $order_service){
        $rs = $order_service->payOrder();
        return $rs['status']?$this->success($rs['data']):$this->error($rs['msg']);
    }

    // 修改订单状态 // 用户只能操作取消订单
    public function edit_order_status(Request $request, OrderService $order_service){
        $order_service->editOrderStatus($request->id, $request->order_status, 'api');
        return $this->success();
    }

    // 获取订单详情
    public function get_order_info($id){
        $order_service = new OrderService();
        $info = $order_service->getOrderInfoById($id, 'api');
        return $this->success($info);
    }
}
