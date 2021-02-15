<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GoodsCateService;
use Illuminate\Http\Request;

class GoodsCateController extends Controller
{
    // 获取商品分类
    public function index(GoodsCateService $goods_cate_service){
        $info = $goods_cate_service->getGoodsCate();
        return $this->success($info);
    }
}
