<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SmsService;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Http\Requests\Api\VerificationCodeRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Requests\Api\LoginRequest;

class AuthController extends Controller
{

    /**
     * 登录接口
     *
     * @param 
     * @return void
     * @Description
     * @author dj 2021-1-29
     */    
    public function login(LoginRequest $request){
        $user_service = new UserService();
        $info = $user_service->login();
        return $this->success($info);
    }

    // 检测是否登陆
    public function check_login(UserService $user_service){
        $info = $user_service->checkLogin('user');
        return $info['status']?$this->success($info['data']):$this->error($info['msg']);
    }

    /**
     * 注册接口
     *
     * @param 
     * @return void
     * @Description
     * @author dj 2021-1-29
     */
    public function register(RegisterRequest $request){
        $user_service = new UserService();
        $info = $user_service->register($request);
        return $this->success($info);
    }

    // 找回密码
    public function forget_password(){
        $user_service = new UserService();
        $rs = $user_service->forgetPassword('phone');
        return $rs['status']?$this->success($rs['data'],$rs['msg']):$this->error($rs['msg']);
    }


    /**
     * 发送短信接口
     *
     * @param 
     * @return void
     * @Description
     * @author dj 2021-1-29
     */
    public function send_sms(VerificationCodeRequest $request){
        $sms_service = new SmsService();
        $info = $sms_service->sendSms($request->mobile, $request->type);
        return $this->success();
    }

    // 退出账号
    public function logout(){
        try{
            auth('user')->logout();
        }catch(\Exception $e){
            return $this->success([],__('base.success'));
        }
        return $this->success([],__('base.success'));
    }
}
