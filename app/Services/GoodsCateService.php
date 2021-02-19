<?php
namespace App\Services;

use App\Http\Resources\Home\GoodsResource\GoodsListCollection;
use App\Models\Goods;
use App\Models\GoodsCate;
use App\Traits\HelperTrait;

class GoodsCateService extends BaseService{
    use HelperTrait;


    public function getGoodsCate(){
        $goods_class_list = GoodsCate::query()->orderBy('is_sort','asc')->get()->toArray();
        $list = $this->getChildren($goods_class_list);
        return $list;
    }

    // 获取一级栏目下所有商品信息 is_matser = 1  $goods_num ,获取数量
    public function is_master($goods_num = 6){
        $goods_model = new Goods();
        $goods_class_list = $this->getGoodsCate();
        $class_goods = [];
        foreach($goods_class_list as $k=>$v){
            $class_goods[$k]['name'] = $v['name'];
            $class_goods[$k]['id'] = $v['id'];
            $class_goods[$k]['goods'] = [];
            $class_goods[$k]['class_id'] = [];
            foreach($v['children'] as $vo){
                if(isset($vo['children'])){
                    foreach($vo['children'] as $item){
                        $class_goods[$k]['class_id'][] = $item['id'];
                    }
                }
                
            }
        }

        foreach($class_goods as &$v){
            $v['goods'] = new GoodsListCollection($goods_model->whereHas('store',function($q){
                return $q->where(['store_status'=>1,'store_verify'=>3]);
            })->with(['goods_skus'=>function($q){
                return $q->orderBy('goods_price','asc');
            }])->where(['goods_status'=>1,'goods_verify'=>1])->whereIn('class_id',$v['class_id'])->take($goods_num)->get());
            unset($v['class_id']);
        }

        return $class_goods;
    }

    // 根据商品ID 获取分类信息
    public function getGoodsCateByGoodsId($id){
        $goods_model = new Goods;
        $goods_cate_model = new GoodsCate;
        $goods_info = $goods_model->find($id);
        $first_cate = $goods_cate_model->select('id','pid','name')->where('id',$goods_info['cate_id'])->first();
        $sec_cate = $goods_cate_model->select('id','pid','name')->where('id',$first_cate['pid'])->first();
        $tr_cate = $goods_cate_model->select('id','pid','name')->where('id',$sec_cate['pid'])->first();
        $data = [$tr_cate,$sec_cate,$first_cate];
        return $data;
    }
}
