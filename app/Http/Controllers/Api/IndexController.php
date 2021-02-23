<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AdvService;
use App\Services\GoodsService;
use App\Services\SeckillService;
use App\Services\CouponService;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    // 获取首页信息
    public function index(GoodsService $goods_service,AdvService $adv_service,SeckillService $seckill_service,CouponService $coupon_service){
        $info['goods'] = $goods_service->getHomeMasterGoods(); // 获取首页主推商品

        // dd($info);
        $info['coupons'] = $coupon_service->getCoupon(false ,'id,name,money,type');
        $info['banners'] = $adv_service->getAdvList('API_首页Banner');
        $info['test'] = ['dj' => '666', 'child' => ['n1' => 'q','n2' => 'qq'], 'fml' => '777','djfml' => '888'];      
        // dd($info);  
        // $info->test = 666;
        // $data['seckill_list'] = $seckill_service->getIndexSeckillAndGoods(4)['data'];
        return $this->success($info);
    }
}
