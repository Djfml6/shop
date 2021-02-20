<?php
namespace App\Services;

use App\Http\Resources\Api\AdvResource\AdvCollection;
use App\Models\Adv;
use App\Models\AdvPosition;

class AdvService extends BaseService{
    
    // 获取指定名称广告
    public function getAdvList($name=''){
        $adv_position_model = new AdvPosition();
        $list = $adv_position_model->where('ap_name', $name)->first()->adv()->whereDate('adv_start','<',date('Y-m-d H:i:s'))->whereDate('adv_end','>',date('Y-m-d H:i:s'))->get();
        return new AdvCollection($list);
    }
}
