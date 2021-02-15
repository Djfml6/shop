<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\OrderResource\OrderCollection;
use App\Http\Resources\Api\UserResource\UserEditResource;
use App\Http\Resources\Api\UserResource\UserResource;
use App\Models\Order;
use App\Services\UserService;
use App\Models\UserCheck;
use App\Models\UserWechat;
use App\Services\FavoriteService;
use App\Services\OrderService;
use App\Services\UploadService;
use Illuminate\Http\Request;
use App\Http\Constant;

class UserController extends Controller
{
    public function info(Request $request){
        $user_service = new UserService();
        $user_info = $user_service->getUserInfo();
        // 获取资料完成度
        $suc = 0;
        $all = 0;
        foreach($user_info as $v){
            if(!empty($v)){
                $suc++;
            }
            $all++;
        }
        $user_info['completion'] = intval($suc/$all*100);
        $user_info['user_check'] = UserCheck::where('user_id',$user_info['id'])->exists();
        $user_info['wechat_check'] = UserWechat::where('user_id',$user_info['id'])->exists();
        $user_info = new UserResource($user_info);
        return $this->success($user_info);
    }

    public function edit_user(Request $request){
        $user_service = new UserService();
        if($request->isMethod('put')){
            $rs = $user_service->editUser();
            return $rs['status']?$this->success($rs['data'],__('users.edit_success')):$this->error(__('users.edit_error'));
        }
        $user_info = $user_service->getUserInfo();
        return $this->success(new UserEditResource($user_info));
    }

    // 个人中心首页默认信息
    public function default(Request $request){
        // 用户信息
        $user_service = new UserService();
        $user_info = $user_service->getUserInfo();

        $data = [];
        // 获取订单数量
        $order_model = new Order();
        $data['count']['wait_pay'] = $order_model->where(['user_id' => $user_info['id'], 'order_status' => Constant::ORDER_STATUS_WAITPAY])->count();
        $data['count']['wait_rec'] = $order_model->where(['user_id' => $user_info['id'], 'order_status' => Constant::ORDER_STATUS_WAITREC])->count();
        $data['count']['confirm'] = $order_model->where(['user_id' => $user_info['id'], 'order_status' => Constant::ORDER_STATUS_CONFIRM])->count();
        $data['count']['wait_comment'] = $order_model->where(['user_id' => $user_info['id'], 'order_status' => Constant::ORDER_STATUS_WAITCOMMENT])->count();
        $data['count']['service'] = $order_model->where(['user_id' => $user_info['id'], 'order_status' => Constant::ORDER_STATUS_SERVICE])->count();

        $data['user'] = $user_info;
        return $this->success($data);
    }

    // 图片上传
    public function avatar_upload(UploadService $upload_service){
        $user_service = new UserService();
        $user_info = $user_service->getUserInfo();
        $rs = $upload_service->avatar($user_info['id']);
        if($rs['status']){
            return $this->success($rs['data'],$rs['msg']);
        }else{
            return $this->error($rs['msg']);
        }
    }
}
