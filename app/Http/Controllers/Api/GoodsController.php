<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GroupService;
use App\Services\CouponService;
use App\Services\FullReductionService;
use App\Services\GoodsService;
use App\Services\OrderCommentService;
use App\Services\SeckillService;
use App\Services\StoreService;
use Illuminate\Http\Request;

class GoodsController extends Controller
{
    // 商品详情
    public function show(GoodsService $goods_service,
                                StoreService $store_service,
                                CouponService $coupon_service,
                                FullReductionService $full_reduction_service,
                                SeckillService $seckill_service,
                                GroupService $group_service, $id){
        $goods_info = $goods_service->getGoodsInfo($id);
        // if($goods_info['status']){
        $goods_info['store_info'] = $store_service->getStoreInfoAndRate($goods_info['store_id'],'id,store_name,store_company_name,area_info,store_address,after_sale_service');
        $goods_info['sale_list'] = $goods_service->getStoreSaleGoods(['store_id' => $goods_info['store_info']['id'], 'cate_id'=>$goods_info['cate_id']]); // 商品销售排名
            $goods_info['coupon_list'] = $coupon_service->getCouponByStoreId($goods_info['store_info']['id']); // 优惠券
            $goods_info['full_reductions'] = $full_reduction_service->getFullReductionByStoreId($goods_info['store_id'])['data']; // 满减
            // $seckill_info = $seckill_service->getSeckillInfoByGoodsId($id);
            // $goods_info['seckills'] = $seckill_info['status']?$seckill_info['data']:false; // 秒杀
            $group_info = $group_service->getGroupInfoByGoodsId($id);
            $goods_info['group'] = $group_info ?? false; // 团购
            $goods_info['group_log'] = $group_service->getGroupLogByGoodsId($id); // 正在进行的团购

        // }
        return $this->success($goods_info);
        
    }


    // 评论统计
    public function comment_count(OrderCommentService $ocs,$id){
        $info = $ocs->getCommentStatistics($id);
        return $this->success($info);
    }

    // 评论列表
    public function comment(OrderCommentService $ocs,$id){
        $info = $ocs->getList($id);
        return $this->success($info);
    }

    // 搜索产品
    public function search(GoodsService $goods_service){
        $info = $goods_service->goodsSearch();
        return $this->success($info);
    }
}
