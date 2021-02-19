<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AdvService;
use App\Services\GoodsService;
use App\Services\SeckillService;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    // 获取首页信息
    public function index(GoodsService $goods_service,AdvService $adv_service,SeckillService $seckill_service){
        $data['goods'] = $goods_service->getHomeMasterGoods(); // 获取首页主推商品
        // $data['banner_bottom_adv'] = $adv_service->getAdvList('PC_幻灯片下广告')['data'];
        // $data['banner'] = $adv_service->getAdvList('PC_首页幻灯片')['data'];
        // $data['seckill_list'] = $seckill_service->getIndexSeckillAndGoods(4)['data'];
        // dd($data);
        return $this->success($data);
    }
}
