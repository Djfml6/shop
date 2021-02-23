<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use App\Services\UserService;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Http\Requests\Api\CartRequest;

class CartController extends Controller
{
    // 购物车列表
    public function index(){
        $cart_service = new CartService;
        $info = $cart_service->getCarts();
        return $this->success($info);
    }

    // 添加购物车
    public function store(CartRequest $request){
        $cart_service = new CartService;
        $info = $cart_service->addCart();
        return $this->success();
    }

    // 修改购物车
    public function update(CartRequest $request, $id){
        $cart_service = new CartService;
        $info = $cart_service->editCart($id);
        return $this->success();
    }

    // 删除购物车商品
    public function destroy(Cart $cart_model, $id){
        $idArray = array_filter(explode(',',$id),function($item){
            return is_numeric($item);
        });
        // 获取当前用户user_id
        $user_service = new UserService;
        $user_info = $user_service->getUserInfo();

        $cart_model->whereIn('id', $idArray)->where('user_id', $user_info['id'])->delete();
        return $this->success();
    }

    // 获取购物车数量
    public function count(){
        $cart_service = new CartService;
        $info = $cart_service->getCount();
        return $this->success($info);
    }

    // 购物车状态选中(反选、正选)
    public function checked(CartService $cart_service)
    {
        $cart_service->checked();
        return $this->success([]);
    }

    // 购物车状态选中(全选、全不选)
    public function check_all(CartService $cart_service)
    {
        $cart_service->checkAll();
        return $this->success([]);
    }

}
