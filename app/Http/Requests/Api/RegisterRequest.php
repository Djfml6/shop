<?php

namespace App\Http\Requests\Api;


class RegisterRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'username' => 'required|between:3,25|unique:users,username',
            'password' => 'required|between:6,25',
            'code' => 'required',
            'mobile' => 'required|unique:users,mobile'
        ];
    }

    public function attributes()
    {
        return [
            'username' => '用户名',
            'password' => '密码',
            'code' => '验证码',
            'mobile' => '手机号码'
        ];
    }



}
