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
        $rule = [
            'consignee' => 'required',
            'country_id' => 'required',
            'province_id' => 'required',
            'city_id' => 'required',
            'district_id' => 'required',
            'address_detail' => 'required',
            'area_info' => 'required',
            'mobile' => ['required','regex:/^((13[0-9])|(14[5,7])|(15[0-3,5-9])|(17[0,3,5-8])|(18[0-9])|166|198|199)\d{8}$/']
        ];
        return $rule;

    }

    public function attributes()
    {
        return [
            'id' => '地址id',
            'consignee' => '联系人',
            'country_id' => '国家',
            'province_id' => '城市',
            'city_id' => '城市',
            'district_id' => '县',
            'address_detail' => '详细地址',
            'mobile' => '手机号码',
            'area_info' => '区域'
        ];
    }


}
