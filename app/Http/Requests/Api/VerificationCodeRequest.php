<?php

namespace App\Http\Requests\Api;
use Illuminate\Validation\Rule;

// 手机号码验证
class VerificationCodeRequest extends BaseRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'mobile' => [
                    'required',
                    'regex:/^((13[0-9])|(14[5,7])|(15[0-3,5-9])|(17[0,3,5-8])|(18[0-9])|166|198|199)\d{8}$/'
            ],
            'type' => ['required', Rule::in('register', 'forget_password', 'edit_user')]
        ];
    }


    public function attributes()
    {
        return [
            'mobile' => '手机号码',
            'type' => '发送类型'

        ];
    }
}
