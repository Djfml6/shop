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
use App\Services\UserService;
use App\Services\CartService;
use Illuminate\Http\Request;
use App\Services\FavoriteService;
use App\Http\Constant;

class GoodsController extends Controller
{
    // 商品详情
    public function show(GoodsService $goods_service,
                        StoreService $store_service,
                        CouponService $coupon_service,
                        FullReductionService $full_reduction_service,
                        SeckillService $seckill_service,
                        GroupService $group_service,
                        OrderCommentService $ocs,
                        FavoriteService $favorire_service,
                        UserService $user_service,
                        CartService $cart_service,
                        Request $request,
                        $id){
        $goods_info['goods'] = $goods_service->getGoodsInfo($id)->toArray();
        $goods_info['store_info'] = $store_service->getStoreInfoAndRate($goods_info['goods']['store_id'],'id,store_name,store_company_name,area_info,store_address,after_sale_service');
        $goods_info['sale_list'] = $goods_service->getStoreSaleGoods(['store_id' => $goods_info['store_info']['id'], 'cate_id'=>$goods_info['goods']['cate_id']]); // 商品销售排名
        $goods_info['coupon_list'] = $coupon_service->getCoupon($goods_info['store_info']['id']); // 优惠券
        $goods_info['comment_count'] = $ocs->getCommentStatistics($id);
        // $goods_info['full_reductions'] = $full_reduction_service->getFullReductionByStoreId($goods_info['goods']['store_id'])['data']; // 满减
        // $seckill_info = $seckill_service->getSeckillInfoByGoodsId($id);
        // $goods_info['seckills'] = $seckill_info['status']?$seckill_info['data']:false; // 秒杀
        $group_info = $group_service->getGroupInfoByGoodsId($id);
        $goods_info['group'] = $group_info ?? false; // 团购
        $goods_info['group_log'] = $group_service->getGroupLogByGoodsId($id); // 正在进行的团购


        // 用户如果已登录，查看是否关注了该商品和该店铺,获取该店铺下加入了购物车的商品数
        $goods_info['fav_goods'] = false;
        $goods_info['fav_store'] = false;
        // $goods_info['cart_num'] = 0;
        if($user = $user_service->isLogin())
        {
            $goods_info['fav_goods'] = $favorire_service->isFav($goods_info['goods']['id'], Constant::FAVORITES_TYPE_GOODS);
            $goods_info['fav_store'] = $favorire_service->isFav($goods_info['goods']['store_id'], Constant::FAVORITES_TYPE_STORE);
            $goods_info['store_info']['cart_num'] = $cart_service->getCountByStore($goods_info['goods']['store_id'], $user->id);
            $goods_info['cart_num'] = $cart_service->getCount();
        }

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

    // 切换属性价格和库存
    public function attr(GoodsService $goods_service)
    {
        $info = $goods_service->changeAttr();
        return $this->success($info);
    }
}
