<?php
namespace App\Services;

use App\Http\Resources\Api\GoodsResource\GoodsListCollection;
use App\Http\Resources\Api\GoodsResource\GoodsSearchCollection;
use App\Http\Resources\Api\GoodsResource\SeckillGoodsCollection;
use App\Http\Resources\Api\GoodsResource\StoreGoodsListCollection;
use App\Models\Goods;
use App\Models\GoodsAttr;
use App\Models\GoodsSku;
use App\Models\GoodsSpec;
use App\Traits\HelperTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\CodeResponse;
use App\Exceptions\RequestException;

class GoodsService extends BaseService{
    use HelperTrait;
    protected $status = ['goods_status'=> true,'goods_verify'=>1];
    public function add(){
        $goods_model = new Goods();
        $config_service = new ConfigService;
        $store_id = $this->get_store(true);
        $data = [
            'store_id'              => $store_id??0,
            'goods_name'            => request()->goods_name,                         // 商品名
            'goods_subname'         => request()->goods_subname??'',                  // 副标题
            'goods_no'              => request()->goods_no??'',                       // 商品编号
            'brand_id'              => request()->brand_id,                           // 商品品牌
            'class_id'              => request()->classInfo[2]['id']??0,              // 商品分类
            'goods_master_image'    => request()->goods_master_image,                 // 商品主图
            'goods_price'           => abs(request()->goods_price??0),                // 商品价格
            'goods_market_price'    => abs(request()->goods_market_price??0),         // 商品市场价
            'goods_weight'          => abs(request()->goods_weight??0),               // 商品重量
            'goods_stock'           => abs(request()->goods_stock??0),                // 商品库存
            'goods_content'         => request()->goods_content??'',                  // 商品内容详情
            'goods_content_mobile'  => request()->goods_content_mobile??'',           // 商品内容详情（手机）
            'goods_status'          => abs(request()->goods_status)??0,               // 商品上架状态
            'shipping_id'            => abs(request()->shipping_id)??0,                 // 运费模版ID
            'goods_images'          => implode(',',request()->goods_images??[]),
        ];

        // 判断是否开启添加商品审核
        if(!empty($config_service->getFormatConfig('goods_verify'))){
            $data['goods_verify'] = 2;
        }

        try{
            DB::beginTransaction();
            $goods_model = $goods_model->create($data);
            // 规格处理
            if(isset(request()->skuList) && !empty(request()->skuList)){
                $skuData = [];
                foreach(request()->skuList as $k=>$v){
                    $skuData[$k]['goods_image'] = $v['goods_image']??''; // sku图片
                    $skuData[$k]['spec_id'] = implode(',',$v['spec_id']); // sku 属性
                    $skuData[$k]['sku_name'] = implode(',',$v['sku_name']); // sku名称
                    $skuData[$k]['goods_price'] = abs($v['goods_price']??0); // sku价格
                    $skuData[$k]['goods_market_price'] = abs($v['goods_market_price']??0); // sku市场价
                    $skuData[$k]['goods_stock'] = abs($v['goods_stock']??0); // sku库存
                    $skuData[$k]['goods_weight'] = abs($v['goods_weight']??0); // sku 重量
                }
                $goods_model->goods_skus()->createMany($skuData);
            }
            DB::commit();
            return $this->format([],__('goods.add_success'));
        }catch(\Exception $e){
            DB::rollBack();
            Log::channel('qwlog')->debug('商品添加失败');
            Log::channel('qwlog')->debug($e->getMessage());
            return $this->format_error(__('goods.add_error'));
        }
        
    }

    // 商品编辑
    public function edit($goods_id){
        $goods_model = new Goods();
        $config_service = new ConfigService;
        $goods_skus_model = new GoodsSku;
        $store_id = $this->get_store(true);
        $goods_model = $goods_model->where(['store_id'=>$store_id,'id'=>$goods_id])->first();

        // 商品名
        if(isset(request()->goods_name) && !empty(request()->goods_name)){
            $goods_model->goods_name = request()->goods_name;
        }
        // 副标题
        if(isset(request()->goods_subname) && !empty(request()->goods_subname)){
            $goods_model->goods_subname = request()->goods_subname;
        }
        // 商品编号
        if(isset(request()->goods_no) && !empty(request()->goods_no)){
            $goods_model->goods_no = request()->goods_no;
        }
        // 商品品牌
        if(isset(request()->brand_id) && !empty(request()->brand_id)){
            $goods_model->brand_id = request()->brand_id;
        }
        // 商品分类
        if(isset(request()->classInfo[2]['id']) && !empty(request()->classInfo[2]['id'])){
            $goods_model->class_id = request()->classInfo[2]['id'];
        }
        // 商品主图
        if(isset(request()->goods_master_image) && !empty(request()->goods_master_image)){
            $goods_model->goods_master_image = request()->goods_master_image;
        }
        // 商品价格
        if(isset(request()->goods_price) && !empty(request()->goods_price)){
            $goods_model->goods_price = abs(request()->goods_price??0);
        }
        // 商品市场价
        if(isset(request()->goods_market_price) && !empty(request()->goods_market_price)){
            $goods_model->goods_market_price = abs(request()->goods_market_price??0);
        }
        // 商品重量
        if(isset(request()->goods_weight) && !empty(request()->goods_weight)){
            $goods_model->goods_weight = abs(request()->goods_weight??0);
        }
        // 商品库存
        if(isset(request()->goods_stock) && !empty(request()->goods_stock)){
            $goods_model->goods_stock = abs(request()->goods_stock??0);
        }
        // 商品内容详情
        if(isset(request()->goods_content) && !empty(request()->goods_content)){
            $goods_model->goods_content = request()->goods_content;
        }
        // 商品内容详情（手机）
        if(isset(request()->goods_content_mobile) && !empty(request()->goods_content_mobile)){
            $goods_model->goods_content_mobile = request()->goods_content_mobile;
        }
        // 商品上架状态
        if(isset(request()->goods_status)){
            $goods_model->goods_status = abs(request()->goods_status??0);
        }
        // 商品上架状态
        if(isset(request()->shipping_id)){
            $goods_model->shipping_id = abs(request()->shipping_id??0);
        }
        // 商品图片
        if(isset(request()->goods_images) && !empty(request()->goods_images)){
            $goods_model->goods_images = implode(',',request()->goods_images??[]);
        }

        // 判断是否开启添加商品审核
        if(!empty($config_service->getFormatConfig('goods_verify'))){
            // 如果是内容标题修改则进行审核（暂时不写）
            $goods_model->goods_verify = 2;
        }

        try{
            DB::beginTransaction();
            $goods_model = $goods_model->save();
            // 规格处理
            if(isset(request()->skuList) && !empty(request()->skuList)){
                $skuData = []; 
                $skuId = []; // 修改的skuID 不存在则准备删除
                foreach(request()->skuList as $k=>$v){
                    if(isset($v['id']) && !empty($v['id'])){
                        // 如果ID不为空则代表存在此sku 进行修改
                        $skuId[] = $v['id'];
                        $goods_skus_model->where('goods_id',$goods_id)->where('id',$v['id'])->update([
                            'goods_image'           => $v['goods_image']??'',// sku图片
                            'spec_id'               => implode(',',$v['spec_id']), // sku 属性
                            'sku_name'              => implode(',',$v['sku_name']), // sku名称
                            'goods_price'           => abs($v['goods_price']??0), // sku价格
                            'goods_market_price'    => abs($v['goods_market_price']??0), // sku市场价
                            'goods_stock'           => abs($v['goods_stock']??0), // sku库存
                            'goods_weight'          => abs($v['goods_weight']??0), // sku 重量
                        ]);
                    }else{
                        // 否则进行插入数据库
                        $skuData[$k]['goods_image'] = $v['goods_image']??''; // sku图片
                        $skuData[$k]['spec_id'] = implode(',',$v['spec_id']); // sku 属性
                        $skuData[$k]['sku_name'] = implode(',',$v['sku_name']); // sku名称
                        $skuData[$k]['goods_price'] = abs($v['goods_price']??0); // sku价格
                        $skuData[$k]['goods_market_price'] = abs($v['goods_market_price']??0); // sku市场价
                        $skuData[$k]['goods_stock'] = abs($v['goods_stock']??0); // sku库存
                        $skuData[$k]['goods_weight'] = abs($v['goods_weight']??0); // sku 重量
                    }
                }

                // 如果ID不为空则代表存在此sku 进行修改
                if(!empty($skuId)){
                    $goods_skus_model->where('goods_id',$goods_id)->whereNotIn('id',$skuId)->delete();
                }

                // 新建不存在sku进行插入数据库
                if(!empty($skuData)){
                    $goods_model = new Goods;
                    $goods_model = $goods_model->find($goods_id);
                    $goods_model->goods_skus()->createMany($skuData);
                }
                
            }else{
                // 清空所有sku
                $goods_skus_model->where('goods_id',$goods_id)->delete();
            }
            DB::commit();
            return $this->format([],__('goods.add_success'));
        }catch(\Exception $e){
            DB::rollBack();
            Log::channel('qwlog')->debug('商品编辑失败');
            Log::channel('qwlog')->debug($e->getMessage());
            return $this->format_error(__('goods.add_error'));
        }
        
    }

    // 修改商品的状态审核
    public function editGoodsVerify($goods_id,$status=1,$msg=''){
        $goods_model = new Goods;
        $goods_model = $goods_model->where('id',$goods_id);
        $data = [
            'goods_verify'      =>  $status,
        ];
        if($status == 0){
            $data['refuse_info'] = $msg;
        }
        $rs = $goods_model->update($data);
        return $this->format($rs);
    }

    // 获取商家的商品详情
    public function getStoreGoodsInfo($id){
        $goods_model = new Goods;
        $goods_skus_model = new GoodsSku();
        $goods_attr_model = new GoodsAttr();
        $goods_spec_model = new GoodsSpec();
        $store_id = $this->get_store(true);
        $goods_info = $goods_model->with('goods_brand')->where('store_id',$store_id)->where('id',$id)->first();
        $goods_info['goods_images'] = explode(',',$goods_info['goods_images']);
        
        // 获取处理后的规格信息
        $sku = $goods_skus_model->where('goods_id',$id)->get()->toArray();
        if(!empty($sku)){
            $skuList = [];
            $spec_id = [];
            foreach($sku as $v){
                $v['spec_id'] = explode(',',$v['spec_id']);
                $v['sku_name'] = explode(',',$v['sku_name']);
                $spec_id = array_merge($spec_id,$v['spec_id']);
                $skuList[] = $v;
            }
            $spec_id = array_unique($spec_id);
            $goods_spec = $goods_spec_model->whereIn('id',$spec_id)->orderBy('id','desc')->get()->toArray();
            $attr_id = [];
            foreach($goods_spec as $v){
                if(!in_array($v['attr_id'],$attr_id)){
                    $attr_id[] = $v['attr_id'];
                }
            }
            $goods_attr = $goods_attr_model->whereIn('id',$attr_id)->with('specs')->orderBy('id','desc')->get()->toArray();
            foreach($goods_attr as $k=>$v){
                foreach($v['specs'] as $key=>$vo){
                    if(in_array($vo['id'],$spec_id)){
                        $goods_attr[$k]['specs'][$key]['check'] = true;
                    }
                }
            }
            $goods_info['attrList'] = $goods_attr;
            $goods_info['skuList'] = $skuList;
        }
        return $this->format($goods_info);
    }

    // 获取商品详情
    public function getGoodsInfo($id, $auth = 'api'){
        $goods_model = new Goods;
        $store_service = new StoreService();
        $goods_skus_model = new GoodsSku();
        $goods_attr_model = new GoodsAttr();
        $goods_spec_model = new GoodsSpec();
        if($auth != 'admin'){
            $goods_model = $goods_model->where($this->status);
        }
        $goods_info = $goods_model->with('goods_brand')->where('id',$id)->first();
        if(empty($goods_info)){
            throw new RequestException(CodeResponse::GOODS_INVALID);
        }

        
        // 获取处理后的规格信息
        $sku = $goods_skus_model->where('goods_id',$id)->get()->toArray();
        if(!empty($sku)){
            $skuList = [];
            $spec_id = [];
            $goods_info['init_choose_attr'] = [];
            $goods_info['init_choose_attrname'] = [];
            $goods_info['attrList'] = [];
            $goods_info['init_choose_skuid'] = 0;
            $init_choose_attr = explode(',', $sku[0]['spec_id']); //默认初始化选中的属性
            $init_choose_attrname = explode(',', $sku[0]['sku_name']); //默认初始化选中的属性名称
            foreach($sku as $v){
                $v['spec_id'] = explode(',',$v['spec_id']);
                $spec_id = array_merge($spec_id,$v['spec_id']);
            }
            $init_nothing_spec = array_unique($spec_id);
            $goods_attr = $goods_spec_model->whereIn('id', $init_choose_attr)->with(['attr','self'])->get()->map(function($v, $k) use($init_choose_attr, $init_nothing_spec) {
                    return [
                        'id'       => $v['attr']['id'],
                        'store_id' => $v['attr']['store_id'],
                        'name'     => $v['attr']['name'],
                        'specs'    => $v['self']->map(function($v1, $k1) use($init_choose_attr, $init_nothing_spec) {
                                        if(in_array($v1['id'], $init_choose_attr))
                                        {
                                            $v1['active'] = true;
                                        }else{
                                            $v1['active'] = false;
                                        }
                                        if(in_array($v1['id'], $init_nothing_spec))
                                        {
                                            $v1['nothing'] = false;
                                        }else{
                                            $v1['nothing'] = true;
                                        }
                                        return $v1;
                                    })->toArray()
                    ];
            })->toArray();
            $goods_info['init_choose_attr'] = $init_choose_attr;
            $goods_info['init_choose_attrname'] = $init_choose_attrname;
            $goods_info['goods_price'] = $sku[0]['goods_price'];
            $goods_info['goods_stock'] = $sku[0]['goods_stock'];
            $goods_info['init_choose_skuid'] = $sku[0]['id'];
            $goods_info['attrList'] = $goods_attr;
        }
        $goods_cate_service = new GoodsCateService;
        $goods_info['goods_cate'] = $goods_cate_service->getGoodsCateByGoodsId($id);
      
        return $goods_info;
    }

    // 切换属性价格和库存
    public function changeAttr()
    {
        $spec_id = request()->spec_id;
        $goods_id = request()->goods_id;
        if(!isset($spec_id) || !isset($goods_id))
        {
            throw new RequestException(CodeResponse::VALIDATION_ERROR);
        }
        $goods_model = new Goods;
        $goods_info = $goods_model->where($this->status)->find($goods_id);
        if(!$goods_info)
        {
            throw new RequestException(CodeResponse::GOODS_INVALID);
        }

        $goods_skus_info = $goods_info->goods_skus()->select(DB::raw('id,spec_id,goods_price,goods_market_price,goods_stock,sku_name'))->where('spec_id', implode(',', $spec_id))->first();
        if(!$goods_skus_info)
        {
            throw new RequestException(CodeResponse::GOODS_INVALID);
        }
        if($goods_skus_info['goods_stock'] <= 0)
        {
            throw new RequestException(CodeResponse::GOODS_STOCK_INVALID);
        }
        $goods_skus_info['init_choose_attrname'] = explode(',', $goods_skus_info['sku_name']);
        $goods_skus_info['init_choose_skuid'] = $goods_skus_info['id'];
        return $goods_skus_info;
    }

    // 获取统计数据
    public function getCount($auth="seller"){
        $goods_model = new Goods();

        if($auth == 'seller'){
            $store_id = $this->get_store(true);
            $data = [
                'wait'  =>  $goods_model->where('goods_verify',2)->where('store_id',$store_id)->count(),
                'refuse'  =>  $goods_model->where('goods_verify',0)->where('store_id',$store_id)->count(),
            ];
        }else{
            $data = [
                'wait'  =>  $goods_model->where('goods_verify',2)->count(),
                'refuse'  =>  $goods_model->where('goods_verify',0)->count(),
            ];
        }
        
        return $data;
    }

    // 搜索
    public function goodsSearch(){
        $goods_model = Goods::query();

        $params_array = request()->post() ?? '';

        try{

            $list = $goods_model->when(isset($params_array['brand_id']) && !empty($params_array['brand_id']), function($query)use($params_array){
                    $query->where('brand_id',$params_array['brand_id']);
            })->when(isset($params_array['cate_id']) && !empty($params_array['cate_id']), function($query)use($params_array){
                    $query->where('cate_id',$params_array['cate_id']);
            })->when(isset($params_array['cate_id']) && !empty($params_array['cate_id']), function($query)use($params_array){
                    $query->where('cate_id',$params_array['cate_id']);
            })->when(isset($params_array['store_id']) && !empty($params_array['store_id']), function($query)use($params_array){
                    $query->where('store_id',$params_array['store_id']);
            })->when(isset($params_array['keywords']) && !empty($params_array['keywords']), function($query)use($params_array){
                    $params_array['keywords'] = urldecode($params_array['keywords']);
                    $query->where('goods_name','like','%'.$params_array['keywords'].'%');
            })->when(isset($params_array['store_id']) && !empty($params_array['store_id']), function($query)use($params_array){
                    $query->where('store_id',$params_array['store_id']);
            })->when(isset(request()->is_group) && !empty(request()->is_group), function($query)use($params_array){
                    $query->whereHas('group');
            })->where($this->status)->with(['goods_sku'=>function($q){
                    return $q->select('goods_id','goods_price','goods_stock')->orderBy('goods_price','asc');
            }])->withCount('order_comment')->whereHas('store',function($q){
                    return $q->where(['store_status' => true]);
            })->paginate(request()->post('per_page') ?? 30);



        }catch(\Exception $e){
            Log::channel('qwlog')->debug($e->getMessage());
            throw new RequestException(CodeResponse::GOODS_INVALID);
            
        }
        return new GoodsSearchCollection($list);

    }

    // 获取门店指定条件销售排行
    public function getStoreSaleGoods($where,$take=6){
        $list = Goods::query()->whereHas('store',function($q){
            return $q->where(['store_status' => true]);
        })->with(['goods_skus'=>function($q){
            return $q->orderBy('goods_price','asc');
        }])->where($where)->where($this->status)->take($take)->orderBy('goods_sale','desc')->get();
        return $list;
    }

    // 商家首页获取商品列表
    public function getHomeStoreGoods($id){
        $goods_model = new Goods;

        $list = $goods_model->where('store_id', $id)->where($this->status)
                ->with(['goods_sku'=>function($q){
                    return $q->select('goods_id','goods_price','goods_stock','goods_market_price')->orderBy('goods_price','asc');
                }])
                ->paginate(request()->per_page??30);

        return $list;
    }

    // 获取首页秒杀商品
    public function getHomeSeckillGoods(){
        $goods_model = new Goods;
        $list = $goods_model->where($this->status)
                        ->with(['goods_sku'=>function($q){
                            return $q->select('goods_id','goods_price','goods_stock','goods_market_price')->orderBy('goods_price','asc');
                        }])
                        ->whereHas('seckill',function($q){
                            if(empty(request()->start_time)){
                                $q->where('start_time',now()->format('Y-m-d H').':00');
                            }
                            $q->where('start_time',now()->addHours(request()->start_time)->format('Y-m-d H').':00');
                        })
                        ->paginate(request()->per_page??30);
        return $this->format(new SeckillGoodsCollection($list));
    }

    // 获取首页主推商品
    public function getHomeMasterGoods()
    {
        $goods_model = new Goods();
        $list = $goods_model->where($this->status)->with('goods_sku')->take(10)->get();
        return (new GoodsListCollection($list));
    }
}
