<?php

namespace App\Http\Requests\Api;


class LoginRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'username' => 'required',
            'password' => 'required'
        ];
    }

    public function attributes()
    {
        return [
            'username' => '用户名',
            'password' => '密码'
        ];
    }
}
