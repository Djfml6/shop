<?php
namespace App\Services;

use App\Models\Config;
use App\Models\SmsSign;

class ConfigService extends BaseService{
    
    // 获取格式化配置[或者某个选项的配置信息]
    public function getFormatConfig($name = ''){
        $list = Config::query()->get();
        $data = [];
        foreach($list as $v){
            $data[$v['name']] = $v['val'];
        }

        $data = $this->pay_format($data);
        if(empty($name)){
            return $data;
        }
        return $data[$name];
    }


    public function pay_format($data){
        if(empty($data['wechat_pay'])){
            $info = [
                'app_id'=>'',
                'app_secret'=>'',
                'mch_id'=>'',
                'key'=>'',
                'notify_url'=>'',
            ];
            $data['wechat_pay'] = [
                'wechat_h5'=>$info,
                'wechat_public'=>$info,
                'wechat_app'=>$info,
                'wechat_mini'=>$info,
                'wechat_scan'=>$info,
            ];
        }else{
            $data['wechat_pay'] = json_decode($data['wechat_pay'],true);
        }
        if(empty($data['ali_pay'])){
            $info = [
                'app_id'=>'',
                'public_key'=>'',
                'private_key'=>'',
                'return_url'=>'',
                'notify_url'=>'',
            ];
            $data['ali_pay'] = [
                'ali_h5'=>$info,
                'ali_app'=>$info,
                'ali_mini'=>$info,
                'ali_scan'=>$info,
            ];
        }else{
            $data['ali_pay'] = json_decode($data['ali_pay'],true);
        }
        if(empty($data['alioss'])){
            $data['alioss'] = [
                'status'=>false,
                'access_id'=>'',
                'access_key'=>'',
                'bucket'=>'',
                'endpoint'=>'',
                'cdnDomain'=>'',
                'ssl'=>false,
                'isCName'=>false,
            ];
        }else{
            $data['alioss'] = json_decode($data['alioss'],true);
        }


        if(empty($data['alisms'])){
            $data['alisms'] = [
                'key'=>'',
                'secret'=>'',
                "register" => [
                    "type" => "",
                    "sign_name" => "",
                    "template" => "",
                    "content" => ""
                ]
            ];
        }else{
            $data['alisms'] = json_decode($data['alisms'],true);
        }

        if(empty($data['kuaibao'])){
            $data['kuaibao'] = [
                'app_id'=>'',
                'app_key'=>'',
            ];
        }else{
            $data['kuaibao'] = json_decode($data['kuaibao'],true);
        }

        // 自动任务
        if(empty($data['task'])){
            $data['task'] = [
                'confirm'=>'5', // 完成订单
                'cancel'=>'1', // 取消
                'settlement'=>'7', // 结算
            ];
        }else{
            $data['task'] = json_decode($data['task'],true);
        }

        // 积分配置
        if(empty($data['integral'])){
            $data['integral'] = [
                'login'=>[
                    'status'=>true,
                    'integral'=>1,
                ], 
                'register'=>[
                    'status'=>true,
                    'integral'=>2,
                ], 
                'inviter'=>[
                    'status'=>false,
                    'integral'=>1,
                ], 
                'order'=>[
                    'status'=>false,
                    'integral'=>1,
                ], 
              
            ];
        }else{
            $data['integral'] = json_decode($data['integral'],true);
        }

        // oauth 第三方登录
        if(empty($data['oauth'])){
            $data['oauth'] = [
                'weixinweb'=>[
                    'client_id'=>'',
                    'client_secret'=>'',
                    'redirect'=>'',
                ], 
            ];
        }else{
            $data['oauth'] = json_decode($data['oauth'],true);
        }

        return $data;
    }



}