<?php
namespace App\Services;

use App\Http\Resources\Home\groupLogResourcee\groupLogGoodsCollection;
use App\Models\Group;
use App\Models\GroupLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Constant;

class GroupService extends BaseService{
    // 根据商品ID 获取拼团详情
    public function getGroupInfoByGoodsId($goods_id){
        $info = Group::query()->where('goods_id',$goods_id)->first();
        return $info;
    }

    // 根据商品ID获取当前的进行中的团购
    public function getGroupLogByGoodsId($goods_id){
        $group_log_model = new GroupLog();
        $list = $group_log_model->where('goods_id',$goods_id)->where('status', Constant::GROUPLOG_STATUS_ING)->withCount('orders')->with('user')->get();

        return $list;
    }

    // 根据ID 和订单ID创建日志 group_id 是日志ID group_resp 团信息 order_goods 订单商品信息
    public function createGroupLog($group_id,$group_resp,$order_goods){
        try{
            DB::beginTransaction();
            if($group_id>0){
                $group_log_model = new GroupLog();
                $cli = $group_log_model->where('id',$group_id)->withCount('orders')->first();
                if($cli->orders_count>=$cli->need){ // 满了
                    throw new \Exception(__('markets.group_is_full'));
                }
                if($cli->orders_count+1 == $cli->need){
                    $group_log_model->status=1;
                    $group_log_model->save();
                }
            }
            if($group_id<0){
                $group_log_model = new groupLog();
                $data = [
                    'user_id' => $order_goods['user_id'],
                    'store_id' => $order_goods['store_id'],
                    'goods_id' => $order_goods['goods_id'],
                    'group_id' => $group_resp['data']['id'],
                    'discount' => $group_resp['data']['discount'],
                    'need' => $group_resp['data']['need'],
                    'status' => 2,
                ];
                $group_log_info = $group_log_model->create($data);
                $group_id = $group_log_info->id;
            }
            DB::commit();
            return $group_id;
        }catch(\Exception $e){
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
        
    }
}
