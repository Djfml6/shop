<?php

namespace App\Http\Requests\Api;


class GoodsRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'cate_id' => 'integer|digits_between:1,12',
            'brand_id' => 'integer|digits_between:1,12',
            'keywords' => 'string',
            'is_new' => 'boolean',
            'is_host' => 'boolean',
            'sort' => ['regex:/^(price|created_at)_(desc|asc)$/i']
        ];
    }

    public function attributes()
    {
        return [
            'cate_id' => '分类id',
            'brand_id' => '品牌id',
            'keywords' => '关键字',
            'is_new' => '新品排序',
            'is_host' => '热度排序',
            'sort' => '排序'
        ];
    }
}
