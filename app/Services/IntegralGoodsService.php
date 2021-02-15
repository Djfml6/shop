<?php
namespace App\Services;

use App\Models\IntegralGoods;
use App\Http\CodeResponse;
use App\Exceptions\RequestException;

class IntegralGoodsService extends BaseService{

    public function search(){
        $ig_model = new IntegralGoods();
        $params_array = request()->post()??'';

        try{
    
            $list = $ig_model->when(isset($params_array['cid']) && !empty($params_array['cid']), function($query) use($params_array){
                $query->where('cid',$params_array['cid']);
            })->where('goods_status', true)->paginate(request()->post('per_page') ?? 30);
        }catch(\Exception $e){
            throw new RequestException(CodeResponse::GOODS_INVALID);
        }
        return $list;
    }
}
