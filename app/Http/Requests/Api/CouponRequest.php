<?php

namespace App\Http\Requests\Api;

class CouponRequest extends BaseRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id' => ['required', 'integer']
        ];
    }

    public function attributes()
    {
        return [
            'id' => '优惠券id'
        ];
    }
}
