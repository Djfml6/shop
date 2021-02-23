<?php

namespace App\Http\Requests\Api;

use App\Models\Goods;
use App\Http\Constant;
use Illuminate\Validation\Rule;

class CartRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        switch ($this->method()) {
            case 'PUT':
                return [
                    'buy_num' => 'required|integer|min:1'
                ];
                break;
            case 'POST':
                return [
                    'goods_id' => [
                        'required', 
                        'integer', 
                        'min:1', 
                        function($attribute, $value, $next){
                            if(empty(Goods::query()->find($value)))
                            {
                                return $next('商品不存在');
                            }
                        }
                    ],
                    'buy_num' => 'required|integer|min:1',
                    'sku_id' => 'integer|min:1'
                ];
                break;
            default:
                # code...
                break;
        }


    }

    public function attributes()
    {
        return [
            'goods_id' => '商品id',
            'buy_num' => '数量',
            'sku_id' => '商品规格',
            'type' => '操作类型'
        ];
    }
}
