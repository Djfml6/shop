<?php
namespace App\Services;

use App\Models\Address;
use App\Models\Cart;
use App\Models\CouponLog;
use App\Models\Freight;
use App\Models\Goods;
use App\Models\GoodsSku;
use App\Models\Order;
use App\Models\OrderGoods;
use App\Models\OrderPay;
use App\Models\Store;
use App\Models\Shipping;
use App\Traits\HelperTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exceptions\RequestException;
use App\Http\CodeResponse;
use App\Http\Constant;
use Illuminate\Support\Arr;
use App\Http\Resources\Api\OrderResource\OrderResource;
use App\Http\Resources\Api\OrderResource\OrderCollection;

class OrderService extends BaseService{

    use HelperTrait;
    // 要删除的购物车数据
    protected $cartId = [];

    // 获取创建订单前处理订单
    public function createOrderBefore(){
        $params = $this->base64Check();
        $info = $this->createOrderFormat($params);
        return $info;
    }

    // 创建订单
    public function createOrder(){
        $params = $this->base64Check();
        $rs = $this->createOrderFormat($params);


        // 优惠券的处理
        $coupon_id = json_decode(request()->coupon_id, true);

        $user_service = new UserService;
        $user_info = $user_service->getUserInfo();

        // 地址验证
        $address_info = $this->checkAddress($user_info->id);

        
        // 实例化订单表
        $order_model = new Order();
        $order_goods_model = new OrderGoods();
        $coupon_log_model = new CouponLog();
        $seckill_service = new SeckillService;
        $full_reduction_service = new FullReductionService();
        $group_service = new GroupService();

        // 循环生成订单 多个商家则生成多个订单
        try{
            DB::beginTransaction();
                $resp_data = [];
                foreach($rs['order'] as $k => $v){
                    $make_rand = date('YmdHis').$user_info['id'].mt_rand(1000,9999); // 生成订单号
                    $order_data = [
                        'order_no'                  =>  $make_rand, // 订单号
                        'user_id'                   =>  $user_info['id'], // 用户ID
                        'store_id'                  =>  $v['store_info']['id'], // 店铺ID
                        'order_name'                =>  $v['goods_list'][0]['goods_name'], // 商品ID
                        'order_image'               =>  $v['goods_list'][0]['goods_master_image'], // 商品图片
                        'consignee'                 =>  $address_info['consignee'], // 收件人姓名
                        'mobile'                    =>  $address_info['mobile'], // 收件人电话
                        'area'                      =>  $address_info['area_info'], // 收件人地区
                        'address_detail'            =>  $address_info['address_detail'], // 详细地址
                        'coupon_id'                 =>  0, 
                        'remark'                    =>  request()->remark??'', // 备注
                    ];

                    
                    $order_info = $order_model->create($order_data); // 订单数据插入数据库

                    // 初始化其他费用
                    $total_price = 0 ; // 总金额
                    $order_price = 0 ; // 订单总金额
                    $total_weight = 0; // 总重量
                    $freight_money = 0; // 运费
                    $coupon_money = 0; // 优惠券 金额

                    // 循环将订单商品插入
                    foreach($v['goods_list'] as $vo){
                        $order_goods_data = [
                            'order_id'      => $order_info->id, // 订单ID
                            'user_id'       => $order_data['user_id'], // 用户ID
                            'store_id'      => $order_data['store_id'], // 店铺ID
                            'sku_id'        => $vo['sku_id'], // skuid
                            'goods_id'      => $vo['id'], // 商品id
                            'goods_name'    => $vo['goods_name'], // 商品名称
                            'goods_image'   => $vo['goods_master_image'], // 商品图片
                            'sku_name'      => $vo['sku_name'], // sku名称
                            'buy_num'       => $vo['buy_num'], // 购买数量
                            'goods_price'   => $vo['goods_price'], // 商品价格
                            'total_price'   => $vo['total'], // 总价格
                            'total_weight'  => $vo['total_weight'], // 总重量
                        ];

                        // 秒杀
                        // $seckill_info = $seckill_service->getSeckillInfoByGoodsId($order_goods_data['goods_id']);
                        // if($seckill_info['status']){
                        //     $coupon_money += $order_goods_data['total_price']*($seckill_info['data']['discount']/100);
                        // }
                        
                        $order_goods_model->create($order_goods_data); // 插入订单商品表

                        // 开始减去库存
                        $this->orderStock($order_goods_data['goods_id'],$order_goods_data['sku_id'],$order_goods_data['buy_num']);
                        
                        // 将商品总总量加起来
                        $total_weight += $order_goods_data['total_weight'];

                        // 将金额全部计算加载到订单里面
                        $order_price += $order_goods_data['total_price'];
                    }

                    // 获取优惠券的金额 并插入数据库
                    if(!empty($coupon_id[$k]))
                    {   
                        // 店铺是否存在可用的优惠券
                        if(empty($v['coupon_id']))
                        {
                            throw new RequestException(CodeResponse::VALIDATION_ERROR, '店铺不存在可用的优惠券');
                        }

                        // 一个店铺同时只能使用一张优惠券
                        if(!is_numeric($coupon_id[$k]))
                        {
                            throw new RequestException(CodeResponse::VALIDATION_ERROR);
                        }

                        //判断该优惠券是否存在允许使用的优惠券列表中
                        if(!in_array($coupon_id[$k], $v['coupon_id']))
                        {
                            throw new RequestException(CodeResponse::VALIDATION_ERROR, '优惠券无效');
                        }

                        // 开始提取优惠券折扣金额
                        $r = collect($rs['coupons'])->where('id', $coupon_id[$k])->first();
                        $coupon_money = $r['coupon_price'];
                        $cp = $coupon_log_model->find($coupon_id[$k]);
                        $cp->status = 1;
                        $cp->order_id = $order_info->id;
                        $order_info->coupon_id = $coupon_id[$k];
                    }



                    // 满减
                    // $full_reduction_resp = $full_reduction_service->getFullReductionInfoByStoreId($order_data['store_id'],($order_price+$freight_money));
                    // if($full_reduction_resp['status']){
                    //     $coupon_money += $full_reduction_resp['data']['money'];
                    // }

                    // 判断是否是拼团 如果是拼团减去他的金额
                    // $collective_id = $v['goods_list'][0]['collective_id']??0;
                    // if($collective_id != 0){
                    //     $collective_resp = $collective_service->getCollectiveInfoByGoodsId($v['goods_list'][0]['id']);
                    //     if($collective_resp['status']){
                    //         $coupon_money += $order_price*($collective_resp['data']['discount']/100); // 得出拼团减去的钱
                    //     }
                    //     $collective_id = $collective_service->createCollectiveLog($collective_id,$collective_resp,$order_goods_data);
                    // }
                    $freight_money = $this->getFreight($v['goods_list'], $address_info['province_id']);

                    $total_price = $order_price+$freight_money-$coupon_money; // 暂时总金额等于[订单金额+运费-优惠金额]
                    $order_info->total_price = round($total_price,2);
                    $order_info->order_price = $order_price;
                    $order_info->freight_money = $freight_money; // 运费
                    $order_info->coupon_money = $coupon_money; // 优惠金额修改
                    // $order_info->group_log_id = $collective_id; // 团购ID修改 
                    $order_info->save(); // 保存入数据库

                    $resp_data['order_id'][] = $order_info->id;
                    $resp_data['order_no'][] = $make_rand;
                }

                // 执行成功则删除购物车
                // $this->delCart();
            DB::commit();
            return [];
        }catch(\Exception $e){
            Log::channel('qwlog')->debug('createOrder:'.json_encode($e->getMessage()));
            DB::rollBack();
            throw new RequestException(CodeResponse::VALIDATION_ERROR, $e->getMessage());
        }
        

    }

    // 如果是购物车订单，则删除购物车
    private function delCart(){
        if(!empty($this->cartId)){
            $cart_model = new Cart();
            try{
                $cart_model->whereIn('id',$this->cartId)->delete();
            }catch(\Exception $e){
                throw new \Exception(__('orders.order_cart_del_error'));
            }
        }
    }




    // 库存消减增加 is_type 0 减少  1增加
    public function orderStock($goods_id, $sku_id, $num, $is_type = 0){
        
        try{
            if(empty($sku_id) || $sku_id == 0){
                $goods_model = new Goods;
                if(empty($is_type)){
                    $goods_model->where('id', $goods_id)->decrement('goods_stock', $num);
                }else{
                    $goods_model->where('id', $goods_id)->increment('goods_stock', $num);
                }
            }else{
                $goods_sku_model = new GoodsSku();
                if(empty($is_type)){
                    $goods_sku_model->where('id', $sku_id)->decrement('goods_stock', $num);
                }else{
                    $goods_sku_model->where('id', $sku_id)->increment('goods_stock', $num);
                }
            }
        }catch(\Exception $e){
            throw new RequestException(CodeResponse::GOODS_STOCK_INVALID, '商品库存异常');
        }
        
    }

    // 销量消减增加 is_type 0 减少  1增加
    public function orderSale($goods_id,$num,$is_type=0){
        
        try{
            $goods_model = new Goods;
            if(empty($is_type)){
                $goods_model->where('id',$goods_id)->decrement('goods_sale',$num);
            }else{
                $goods_model->where('id',$goods_id)->increment('goods_sale',$num);
            }
        }catch(\Exception $e){
            throw new \Exception(__('base.error').' - stock');
        }
        
    }

    /**
     * // 支付订单 function
     *
     * @param string $order_id 如：10,12,13
     * @param string $payment_name 如：wechat_scan|balance|wechat_h5
     * @param string $pay_password 如：123456 （非必填,payment_name=balance则需要填写)
     * @param string $recharge 如：1 （非必填）
     * @return void
     * @Description
     * @author hg <www.qingwuit.com>
     */
    public function payOrder(){
        $order_id = request()->order_id;
        $payment_name = request()->payment_name??'';

        // 检查支付方式是否传过来
        if(empty($payment_name)){
            return $this->format_error(__('orders.empty_payment'));
        }

        // 判断订单号是否为空
        if(empty($order_id)){
            return $this->format_error(__('orders.error').' - pay');
        }
        $order_arr = explode(',',$order_id); // 转化为数组
        $order_str = implode('',$order_arr); // 转化为字符串生成支付订单号

        // 获取用户信息
        $user_service = new UserService();
        $user_info = $user_service->getUserInfo();
        if(empty($user_info)){
            return $this->format_error(__('user.no_token'));
        }

        // 判断是否订单是该用户的并且订单是否有支付成功过
        $order_model = new Order();
        // 判断是否存在 指定订单
        if(!$order_model->whereIn('id',$order_arr)->where('user_id',$user_info['id'])->exists()){
            return $this->format_error(__('orders.error').' - pay2');
        } 
        // 判断是否已经支付过了
        $order_list = $order_model->whereIn('id',$order_arr)->where('user_id',$user_info['id'])->where('order_status',1)->get();
        if($order_list->isEmpty()){
            return $this->format_error(__('orders.order_pay'));
        }

        // 十秒钟不能重复支付 设计订单支付号 当前时间到秒的十位+用户ID+订单ID号
        $second = substr(date('YmdHis'),0,13);
        $pay_no = $second.$user_info['id'].$order_str; // 订单支付号
        $rs = $this->createPayOrder(false,$user_info,$pay_no,$order_list);

        // 创建支付订单失败
        if(!$rs['status']){
            return $this->format_error($rs['msg']);
        }

        // 获取支付信息,调取第三方支付
        $payment_model = new PayMentService();
        $rs = $payment_model->pay($payment_name,$rs['data']);
        return $rs['status']?$this->format($rs['data']):$this->format_error($rs['msg']);

    }

    // 创建支付订单
    // @param bool $recharge_pay 是否是充值 还是订单
    // @param string $pay_no 支付订单号
    protected function createPayOrder($recharge_pay = false,$user_info,$pay_no='',$order_list=[]){
        // 创建支付订单
        $create_data = [];
        if($recharge_pay){
            $pay_no = date('YmdHis').mt_rand(10000,99999);
            $create_data = [
                'user_id'               =>  $user_info['id'],
                'pay_no'                =>  $pay_no,
                'pay_type'              =>  'r',
                'total_price'           =>  abs(request()->total??1), // 充值金额
            ];
        }else{
            $order_ids = [];
            $total_price = 0;
            $order_balance = 0;
            foreach($order_list as $v){
                $order_ids[] = $v['id'];
                $total_price += $v['total_price']; 
                $order_balance += $v['order_balance']; 
            }
            $create_data = [
                'user_id'                   =>  $user_info['id'],
                'pay_no'                    =>  $pay_no,
                'order_ids'                 =>  implode(',',$order_ids),
                'pay_type'                  =>  'o',
                'total_price'               =>  $total_price, // 订单总金额
                'order_balance'             =>  $order_balance, // 余额支付金额
            ];
        }
        $order_pay_model = new OrderPay();
        
        try{
            $order_pay_info = $order_pay_model->create($create_data);
        }catch(\Exception $e){
            Log::channel('qwlog')->debug($e->getMessage());
            return $this->format_error(__('orders.payment_failed'));
        }

        return $this->format($order_pay_info);
        
    }

    /**
     * 订单状态修改 function
     *
     * @param [type] $order_id 订单ID
     * @param [type] $order_status 订单状态
     * @param [type] $auth 用户操作还是管理员操作 user|admin
     * @return void
     * @Description
     * @author hg <www.qingwuit.com>
     */
    public function editOrderStatus($order_id, $order_status, $auth = "api"){
        $order_model = new Order;
        $order_model = $order_model->where('id',$order_id);
        if($auth == 'api'){
            $user_service = new UserService;
            $user_info = $user_service->getUserInfo();
            // 用户不允许随意操作状态，只能修改 取消订单和确定订单
            if($order_status !=Constant::ORDER_STATUS_CANCLE && $order_status !=Constant::ORDER_STATUS_WAITCOMMENT){
                throw new RequestException(CodeResponse::VALIDATION_ERROR);
            }
            $order_model = $order_model->where('user_id',$user_info['id']);
        }
        $order_model = $order_model->first();

        if(empty($order_model)){
            throw new RequestException(CodeResponse::ORDER_INVALID);
        }

        switch($order_status){
            case 0: // 取消订单
                if($order_model->order_status != Constant::ORDER_STATUS_WAITPAY){ // 只有待支付的订单能取消
                    throw new RequestException(CodeResponse::ORDER_INVALID, '状态异常');
                    
                }
                $og_model = new OrderGoods();
                $og_list = $og_model->select('goods_id','sku_id','buy_num')->where('order_id',$order_id)->get();
                if(!$og_list){
                    throw new RequestException(CodeResponse::ORDER_INVALID);
                }
                foreach($og_list as $v){
                    $this->orderStock($v['goods_id'], $v['sku_id'], $v['buy_num'], 1);
                }
                // 如果有优惠券则修改优惠券
                $coupon_log_model = new CouponLog();
                $coupon_log_model->where('order_id', $order_id)->update(['status' => Constant::COUPON_STATUS_NOT_USED, 'order_id' => 0]);

                // 库存修改
            break;
            case 1: // 等待支付
            break;
            case 2: // 等待发货
            break;
            case 3: // 确认收货
                if(empty($order_model->delivery_no) || empty($order_model->delivery_code)){ // 只有待支付的订单能取消
                    throw new RequestException(CodeResponse::ORDER_INVALID, '状态异常');
                }
            break;
            case 4: // 等待评论
            break;
            case 5: // 5售后
            break;
            case 6: // 6订单完成
            break;
        }
        $order_model->order_status = $order_status;
        $order_model->save();
        return [];
    }

    // 地址验证
    public function checkAddress($user_id){
        $id = request()->address_id ?? 0;
        if(empty($id)){
            throw new RequestException(CodeResponse::VALIDATION_ERROR, '地址不存在');
        }
        $address_info = Address::query()->where(['id' => $id, 'user_id' => $user_id])->first();
        
        if(empty($address_info)){
            throw new RequestException(CodeResponse::VALIDATION_ERROR, '地址不存在');
        }

        return $address_info;
    }


    // 计算运费
    // @param mixed $goods 
    // @param mixed $area_id 省份ID
    protected function getFreight($goods, $area_id)
    {   
        $goods = collect($goods)->groupBy('id')->map(function($item,$key){
            return [
                    'goods_id'    => $item[0]['id'],
                    'shipping_id' => $item[0]['shipping_id'],
                    'weight'      => $item->map(function($_item, $_key){return ['w' => $_item['total_weight']];
                                })->sum('w'),
                    'buy_num'     => $item->map(function($_item, $_key){return ['w' => $_item['buy_num']];})->sum('w'),
            ];
        })->groupBy('shipping_id')->map(function($item,$key){
            return [
                    'goods_id'    => $item[0]['goods_id'],
                    'shipping_id' => $item[0]['shipping_id'],
                    'weight'      => $item->map(function($_item, $_key){return ['w' => $_item['weight']];
                                })->sum('w'),
                    'buy_num'     => $item->map(function($_item, $_key){return ['w' => $_item['buy_num']];})->sum('w'),
            ];                        
        }); 

        $goods_arr = $goods->toArray();

        $info = Shipping::query()->select(['id','is_free','price_method','specify_conditions'])->whereIn('id', $goods->pluck('shipping_id'))->with(['area' => function($query)use($area_id){
                    $query->select(['shipping_id','first_weight','continue_weight','first_num','continue_num','first_price','continue_price'])->whereRaw("FIND_IN_SET($area_id, areas)");
        }, 'free' => function($query)use($area_id){
                    $query->select(['id','shipping_id','weight','num','size'])->whereRaw("FIND_IN_SET($area_id, areas)");
        }])->get()->keyBy('id')->toArray();
        foreach ($goods_arr as &$v) {
            $v = array_merge($v,$info[$v['shipping_id']]);
        }

        // 将免邮的商品剔除[该模板是免运费模板 or 该模板存在部分免运费条件]
        foreach ($goods_arr as $k => &$v) {
            if($v['is_free'] == Constant::SHIPPING_FREE)
            {
                unset($goods_arr[$k]);      
            }

            if(!empty($v['free']))
            {
                if($v['price_method'] == Constant::SHIPPING_PRICE_METHOD_WEIGHT)
                {
                    if($v['free'][0]['size'] == '<' && $v['weight'] < $v['free'][0]['weight'])
                    {
                        unset($goods_arr[$k]);
                    }elseif($v['free'][0]['size'] == '>' && $v['weight'] >= $v['free'][0]['weight'])
                    {
                        unset($goods_arr[$k]);
                    }
                    

                }elseif($v['price_method'] == Constant::SHIPPING_PRICE_METHOD_NUM)
                {
                    if($v['free'][0]['size'] == '<' && $v['buy_num'] < $v['free'][0]['num'])
                    {
                        unset($goods_arr[$k]); 
                    }elseif($v['free'][0]['size'] == '>' && $v['buy_num'] >= $v['free'][0]['num'])
                    {
                        unset($goods_arr[$k]);
                    }

                }
            }

            $v['f_p'] = $v['price_method'] == Constant::SHIPPING_PRICE_METHOD_WEIGHT ? $v['area'][0]['first_price']/$v['area'][0]['first_weight'] :$v['area'][0]['first_price']/$v['area'][0]['first_num'];

        }
        $c = collect($goods_arr);
        $first_template_id = Arr::only($c->where('f_p', $c->max('f_p'))->first(),['goods_id', 'area']);
        $first_template_price = $first_template_id['area'][0]['first_price'];
        $sum = 0;
        // 循环计算增费
        foreach ($goods_arr as $key => &$v) {
            
            if($v['price_method'] == Constant::SHIPPING_PRICE_METHOD_WEIGHT)// 按重量计费
            {
                if($v['goods_id'] == $first_template_id)
                { 
                    if(!empty($v['free']) && $v['free'][0]['size']=='<' && $v['weight']>$v['free'][0]['weight'])
                    {    // 所在模板是首费模板并且符合部分免邮条件情况

                        $sum += ceil(($v['weight'] - 0 - $v['free'][0]['weight'])/$v['area'][0]['continue_weight']*$v['area'][0]['continue_price']);
                    }else{
                        // 所在模板是首费模板并且不符合部分免邮条件情况
                        $sum += ceil(($v['weight'] - $v['area'][0]['first_weight'] - 0)/$v['area'][0]['continue_weight']*$v['area'][0]['continue_price']);
                    }         
                }else{
                    if(!empty($v['free']) && $v['free'][0]['size']=='<' && $v['weight']>$v['free'][0]['weight'])
                    {
                        // 所在模板不是首费模板并且符合部分免邮条件情况
                        $sum += ceil(($v['weight'] - 0 - $v['free'][0]['weight'])/$v['area'][0]['continue_weight']*$v['area'][0]['continue_price']);

                    }else{
                        // 所在模板不是首费模板并且不符合部分免邮条件情况
                        $sum += ceil(($v['weight'] - 0 - 0)/$v['area'][0]['continue_weight']*$v['area'][0]['continue_price']);
                    }
                }
            }elseif($v['price_method'] == Constant::SHIPPING_PRICE_METHOD_NUM){// 按数量计费
                
                if($v['goods_id'] == $first_template_id)
                {
                    if(!empty($v['free']) && $v['free'][0]['size']=='<' && $v['buy_num']>$v['free'][0]['num'])
                    {    // 所在模板是首费模板并且符合部分免邮条件情况

                        $sum += ceil(($v['buy_num'] - 0 - $v['free'][0]['num'])/$v['area'][0]['continue_num']*$v['area'][0]['continue_price']);
                    }else{
                        // 所在模板是首费模板并且不符合部分免邮条件情况
                        $sum += ceil(($v['buy_num'] - $v['area'][0]['first_num'] - 0)/$v['area'][0]['continue_num']*$v['area'][0]['continue_price']);
                    }                      

                }else{
                    if(!empty($v['free']) && $v['free'][0]['size']=='<' && $v['buy_num']>$v['free'][0]['num'])
                    {
                        // 所在模板不是首费模板并且符合部分免邮条件情况
                        $sum += ceil(($v['buy_num'] - 0 - $v['free'][0]['num'])/$v['area'][0]['continue_num']*$v['area'][0]['continue_price']);

                    }else{
                        // 所在模板不是首费模板并且不符合部分免邮条件情况
                        $sum += ceil(($v['buy_num'] - 0 - 0)/$v['area'][0]['continue_num']*$v['area'][0]['continue_price']);
                    }

                }

            }
        }
        return $sum+$first_template_price;
    }


    // 根据订单ID获取商品数据并格式化
    public function createOrderFormat($params){ 
        $goods_model = new Goods();
        $goods_sku_model = new GoodsSku();
        $user_service = new UserService();
        $cart_service = new CartService();
        $user_info = $user_service->getUserInfo();
        $list = [];
        $this->cartId = []; // 购物车ID 初始化
        $res = $cart_service->getCartsByIds($user_info->id, 'id as cart_id,user_id,goods_id,sku_id,store_id,buy_num');
        if(!$res)
        {
            throw new RequestException(CodeResponse::VALIDATION_ERROR);
            
        }
        $params['order'] = $res;

        foreach($params['order'] as $v){
            $data = [];
            $data = $goods_model->with(['store' => function($q){
                return $q->select('id','store_name','store_logo');
            }])->select('id','store_id','goods_name','goods_master_image','goods_price','goods_stock','goods_weight','shipping_id')->where('id', $v['goods_id'])->first();
            if(!$data)
            {
                throw new RequestException(CodeResponse::REQUEST_INVALID);
            }
            $data['sku_name'] = '-';
            $data['buy_num'] = abs(intval($v['buy_num']));
            $data['sku_id'] = $v['sku_id'];
            if($v['sku_id'] > 0){
                $goods_sku = $goods_sku_model->select('id','sku_name','goods_price','goods_stock','goods_weight')->where('goods_id', $v['goods_id'])->where('id', $v['sku_id'])->first();
                if(!$goods_sku)
                {
                    throw new RequestException(CodeResponse::REQUEST_INVALID); 
                }
                $data['sku_name'] = $goods_sku['sku_name'];
                $data['goods_price'] = $goods_sku['goods_price'];
                $data['goods_stock'] = $goods_sku['goods_stock'];
                $data['goods_weight'] = $goods_sku['goods_weight'];
            }

            $data['goods_master_image'] = $data['goods_master_image'];
            $data['total'] = round($v['buy_num'] * $data['goods_price'],2);
            $data['total_weight'] = round($v['buy_num']*$data['goods_weight'],2);
            // 判断是否是团购
            $data['group_id'] = 0;
            if(isset($v['group_id'])){
                $data['group_id'] = $v['group_id']; 
            }
            
            $list[$data['store']['id']]['goods_list'][] = $data->toArray();
            $list[$data['store']['id']]['store_info'] = $data['store']->toArray();

            if(empty($list[$data['store']['id']]['store_total_price'])){
                $list[$data['store']['id']]['store_total_price'] = 0;
            }
            $list[$data['store']['id']]['store_total_price'] += $data['total'];

            // 判断是否库存足够
            if($v['buy_num'] > $data['goods_stock']){
                throw new RequestException(CodeResponse::GOODS_STOCK_INVALID); 
            }

            // 判断是否是购物车
            if(!empty($params['ifcart'])){
                $this->cartId[] = $v['cart_id'];
            }
        }
        $lists['order'] = $list;

        // 循环查看优惠券是否可用 并且计算出当前可用的优惠券优惠的价钱
        $coupon_log_model = new CouponLog();
        $lists['coupon_id'] = [];
        $lists['coupons'] = [];
        foreach($lists['order'] as $k => &$v){
            $coupon_list = $coupon_log_model->with('coupon')->where('user_id', $user_info['id'])->where('store_id', $k)->with(['store'=>function($query){$query->select(['id','store_name']);}])->where('status', Constant::COUPON_STATUS_NOT_USED)->get();
            if(!$coupon_list->isEmpty()){
                foreach ($coupon_list as $key => &$value) {
                    if(now()->lt($value['start_time']) || now()->gt($value['end_time']))
                    {
                        $value['can'] = false;
                        continue;
                    }
                    switch ($value['coupon']['type']) {
                        case Constant::COUPON_TYPE_FIXED: //满减券
                        case Constant::COUPON_TYPE_PERCENT: //折扣券
                            if($value['coupon']['range_type'] == Constant::COUPON_RANGE_ALL)
                            {
                                if($v['store_total_price'] > $value['coupon']['min_money'])
                                {
                                    $value['can'] = true;
                                    $v['coupon_id'][] = $value['id'];
                                    $lists['coupon_id'][] = $value['id'];
                                    if($value['coupon']['type'] == Constant::COUPON_TYPE_FIXED)
                                    {
                                        $value['coupon_price'] = $value['coupon']['money'];
                                    }elseif($value['coupon']['type'] == Constant::COUPON_TYPE_PERCENT){
                                        $diff = $v['store_total_price'] - $v['store_total_price']*($value['coupon']['money']/100);
                                        $value['coupon_price'] = $diff>=$value['coupon']['discount_to_many']?$value['coupon']['discount_to_many']:$diff;

                                    }                                    
                                }else{
                                    $value['can'] = false;
                                }

                                
                            }else{
                                $goods_id = explode(',', $value['coupon']['goods_id']);
                                $intersection = array_map(function($item){return !empty($item)?intval($item):'';},array_intersect($goods_id, collect($v['goods_list'])->groupBy('id')->keys()->toArray()));

                                if(!empty($intersection))
                                {   
                                    $total = collect($v['goods_list'])->whereIn('id',$intersection)->sum('total');                        
                                    if($total > $value['coupon']['min_money'])
                                    {
                                        $value['can'] = true;
                                        $v['coupon_id'][] = $value['id'];
                                        $lists['coupon_id'][] = $value['id'];

                                        if($value['coupon']['type'] == Constant::COUPON_TYPE_FIXED)
                                        {
                                            $value['coupon_price'] = $value['coupon']['money'];
                                        }elseif($value['coupon']['type'] == Constant::COUPON_TYPE_PERCENT){
                                            $diff = $total-$total*($value['coupon']['money']/100);
                                            $value['coupon_price'] = $diff>=$value['coupon']['discount_to_many']?$value['coupon']['discount_to_many']:$diff;
                                        }


                                    }else{
                                        $value['can'] = false;
                                    }
                                }else{
                                    $value['can'] = false;
                                }                                

                            }
                        break;

                        case Constant::COUPON_TYPE_SILL: //无门槛券
                            $value['can'] = true;
                            $lists['coupon_id'][] = $value['id'];
                            $v['coupon_id'][] = $value['id'];
                            $value['coupon_price'] = $value['coupon']['money'];
                        break;

                        default:
                            break;
                    }

                }
                $lists['coupons'][] = $coupon_list->toArray();

            }          
        }

        $lists['coupons'] = collect($lists['coupons'])->collapse()->toArray();
        $lists['order'] = collect($lists['order'])->values();
        return $lists;    
    }

    // base64 代码验证
    public function base64Check(){
        $base64 = request()->input('params') ?? '';
//         $order = [
//             'ifcart' => true,
//             'order' =>[
//                 [
//                     'cart_id' => 1,
//                     'goods_id' => 1,
//                     'buy_num' => 50,
//                     'sku_id' => 2
//                 ],
//                 [
//                     'cart_id' => 2,
//                     'goods_id' => 1,
//                     'buy_num' => 5,
//                     'sku_id' => 5
//                 ],
//                 [
//                     'cart_id' => 6,
//                     'goods_id' => 8,
//                     'buy_num' => 3,
//                     'sku_id' => 8
//                 ],
//                 [
//                     'cart_id' => 7,
//                     'goods_id' => 15,
//                     'buy_num' => 2,
//                     'sku_id' => 10
//                 ], 
//                 [
//                     'cart_id' => 8,
//                     'goods_id' => 15,
//                     'buy_num' => 1,
//                     'sku_id' => 12
//                 ],    
//             ]         

//         ];
// dd(base64_encode(json_encode($order)));
// return base64_encode(json_encode($order));

// die;

        // 如果为空
        if(empty($base64)){
            throw new RequestException(CodeResponse::VALIDATION_ERROR);
        }

        // 判断是否能解析
        try{
            $params = json_decode(base64_decode($base64),true);
        }catch(\Exception $e){
            throw new RequestException(CodeResponse::VALIDATION_ERROR);
            
        }
        return $params;
    }

    // 获取订单
    public function getOrders($type = "api"){
        $order_model = new Order();

        if($type == 'api'){
            $user_service = new UserService;
            $user_info = $user_service->getUserInfo();
            $order_model = $order_model->where('user_id',$user_info['id']);
        }
        if($type == 'seller'){
            $store_id = $this->get_store(true);
            $order_model = $order_model->where('store_id',$store_id);
        }
        
        $order_model = $order_model->with(['store' => function($q){
             return $q->select('id','store_name');
        },'user' => function($q){
            return $q->select('id','username');
        },'order_goods']);
        
        $order_no  = request()->order_no;// 订单号
        $group_log_id = request()->group_log_id;// 拼团订单ID查询
        $created_at = request()->created_at; // 下单时间
        $order_status = request()->order_status;  
        $is_refund = request()->is_refund;   // 获取退款订单 
        $is_return = request()->is_return; // 获取退货订单

        $order_model->when(!empty($order_no), function($query) use($order_no){
            $query->where('order_no','like','%'.$order_no.'%');
        })->when(!empty($group_log_id), function($query) use($group_log_id){
            $query->where('group_log_id',$group_log_id);
        })->when(!empty($created_at), function($query) use($created_at){
            $query->whereBetween('created_at',[$created_at[0],$created_at[1]]);
        })->when(isset($order_status), function($query) use($order_status){
            $query->where('order_status',request()->order_status);
        })->when(isset($is_refund), function($query) use($is_refund){
            $query->where('order_status',5)->where('refund_status',0);
        })->when(isset($is_return), function($query) use($is_return){
            $query->where('order_status',5)->where('refund_status',1);
        });


        $order_model = $order_model->orderBy('id','desc')->paginate(request()->per_page ?? 30);
        return $order_model ? new OrderCollection($order_model) : [];
    }

    // 获取订单信息通过订单ID 默认是需要用用户
    public function getOrderInfoById($id, $auth = 'api'){
        $order_model = new Order();

        if($auth == 'api'){
            $user_service = new UserService;
            $user_info = $user_service->getUserInfo();
            $order_model = $order_model->where('user_id', $user_info['id']);
        }

        if($auth == 'seller'){
            $store_id = $this->get_store(true);
            $order_model = $order_model->where('store_id', $store_id);
        }

        $order_info = $order_model->with('order_goods')->where('id', $id)->first();
        if(!$order_info)
        {
            throw new RequestException(CodeResponse::ORDER_INVALID);
            
        }
        return new OrderResource($order_info);
    }



    
}
