<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'consignee' => 'required',
            'country' => 'required',
            'province' => 'required',
            'city' => 'required',
            'district' => 'required',
            'address_detail' => 'required',
            'house_number' => 'required',
            'mobile' => ['required','regex:/^((13[0-9])|(14[5,7])|(15[0-3,5-9])|(17[0,3,5-8])|(18[0-9])|166|198|199)\d{8}$/'],
        ];
    }

    public function attributes()
    {
        return [
            'consignee' => '联系人',
            'country' => '国家',
            'province' => '城市',
            'city' => '城市',
            'district' => '县',
            'address_detail' => '详细地址',
            'mobile' => '手机号码',
            'house_number' => '门牌号'
        ];
    }


}
