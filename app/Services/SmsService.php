<?php
namespace App\Services;

use Carbon\Carbon;
use App\Models\SmsLog;
use App\Models\User;
use Overtrue\EasySms\EasySms;
use Illuminate\Support\Facades\Cache;
use App\Http\CodeResponse;
use App\Exceptions\RequestException;

class SmsService extends BaseService{
    private $configs = [
    ];

    /**
     * 发送验证码
     *
     * @param Config $config_model
     * @param [String] $mobile  手机号码
     * @param [String] $type  短信类型
     * @return void
     * @Description 发送短信
     * @author dj
     */
    public function sendSms($mobile, $type)
    {
    	
        $this->sendBefore($mobile,$type);
        $config_service = new ConfigService();
        $alisms = $config_service->getFormatConfig('alisms');

        $this->configs = config('easysms');
        $this->configs['gateways']['aliyun']['access_key_id'] = $alisms['key'];
        $this->configs['gateways']['aliyun']['access_key_secret'] = $alisms['secret'];
        $this->configs['gateways']['aliyun']['sign_name'] = $alisms[$type]['sign_name'];
        $easySms = new EasySms($this->configs);



        $code = str_pad(random_int(1, 99999), 5, 0, STR_PAD_LEFT);

        



        try{
            // 插入日志
            $sms_log_model = SmsLog::create([
                'mobile'     =>  $mobile,
                'content'      =>  $code,
                'status'    =>  1,  
                'type'      =>  $type,
                'ip'        =>  request()->getClientIp(),
            ]);

            if(app()->environment('production'))
            {
                 $rs = $easySms->send($mobile, [
                    'content'  => '',
                    'template' => $alisms[$type]['template'],
                    'data' => ['code' => $code],
                ]);         
            }else{
                $rs['aliyun']['status'] ='success';
                $rs['aliyun']['result']['Code'] = 'OK';
            }


        } catch (\Overtrue\EasySms\Exceptions\NoGatewayAvailableException $e) {
            $sms_log_model->error_msg = $e->getException('aliyun')->getMessage();
            $sms_log_model->status = 0;
            $sms_log_model->save();
            throw new RequestException(CodeResponse::NO_GATEWAY_AVAILABLE);
        }
        

        if(isset($rs) && $rs['aliyun']['status'] == 'success' && $rs['aliyun']['result']['Code'] == 'OK'){
            Cache::put("{$type}_captcha_".$mobile, $code, 6000);
        }else{
            $sms_log_model->error_msg = json_encode($rs);
            $sms_log_model->status = 0;
            $sms_log_model->save();
            throw new RequestException(CodeResponse::NO_GATEWAY_AVAILABLE);
        }
        
    }

    // 短信判断是否能发送
    public function sendBefore($mobile, $type){

        // 如果是注册则判断是否存在此账号
        if($type == 'register'){
            if(User::query()->where('mobile',$mobile)->exists()){
                throw new RequestException(CodeResponse::USER_INVALID, "用户已存在");
            }
        }


        // 忘记密码 和 修改资料的时候判断是否存在
        if($type == 'forget_password' || $type == 'edit_user'){
            if(!User::query()->where('mobile',$mobile)->exists()){
                throw new RequestException(CodeResponse::USER_INVALID, "用户已存在");
            }
        }


        // 注册发送、忘记密码发送  防刷机制，一分钟只能触发一次，当天只能触发10次
        $lock = Cache::add("{$type}_captcha_lock_".$mobile, 1, 60);

        if(!$lock)
        {
            throw new RequestException(CodeResponse::REGISTER_CAPTCHA_ERROR, "60秒后重试");
        }

        $countKey = "{$type}_captcha_count_".$mobile;
        if(Cache::has($countKey))
        {
            $count = Cache::increment($countKey);
            if($count >= 10)
            {
                throw new RequestException(CodeResponse::REGISTER_CAPTCHA_ERROR,'验证码次数用尽');
            }

        }else{
            Cache::put($countKey, 1, Carbon::tomorrow()->diffInSeconds(now()));
        }
    }


    /**
     *  检查验证码是否正确
     */
    public function checkCaptcha($mobile, $code, $type)
    {
        if(empty($mobile) || empty($code))
        {
            throw new RequestException(CodeResponse::VALIDATION_ERROR);
        }
        $key = "{$type}_captcha_".$mobile;

        $captcha = Cache::get($key);

        if($captcha !== $code)
        {
            throw new RequestException(CodeResponse::REGISTER_CAPTCHA_ERROR, '验证码错误');
        }
        Cache::forget($key);

    }
}
