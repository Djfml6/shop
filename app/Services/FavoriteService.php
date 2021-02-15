<?php
namespace App\Services;

use App\Http\Resources\Api\FavoriteResource\FavoriteCollection;
use App\Http\Resources\Api\FavoriteResource\FollowCollection;
use App\Http\Constant;
use App\Models\Favorite;
use App\Exceptions\RequestException;
use App\Http\CodeResponse;

class FavoriteService extends BaseService{

    public function getFav(){
        $type = request()->type;
        $user_service = new UserService;
        $user_info = $user_service->getUserInfo();
        $fav_model = Favorite::query()->where(['user_id' => $user_info->id, 'is_type' => $type]);
        
        if($type == Constant::FAVORITES_TYPE_GOODS){

            $fav_model = $fav_model->with(['goods'=>function($q){
                return $q->select('id','goods_master_image','goods_price','goods_name','goods_subname')->with('goods_sku');
            }]);

            $fav_list = $fav_model->paginate(request()->per_page ?? 30);
            return new FavoriteCollection($fav_list);
        }else{

            $fav_model = $fav_model->with(['store'=>function($q){
                return $q->select('id','store_name','store_logo');
            }]);
            $fav_list = $fav_model->paginate(request()->per_page ?? 30);
            return new FollowCollection($fav_list);
        }
        
        
    }

    // 添加收藏和关注
    public function addOrDelFav($out_id){
        if(!in_array(request()->type,[Constant::FAVORITES_TYPE_GOODS, Constant::FAVORITES_TYPE_STORE])){
            throw new RequestException(CodeResponse::VALIDATION_ERROR);
        }

        $user_service = new UserService;
        $user_info = $user_service->getUserInfo();
        
        $fav_model = new Favorite();
        $fav_info = $fav_model->where(['user_id'=>$user_info['id'],'out_id'=>$out_id,'is_type'=>request()->type])->first();
        if(!empty($fav_info)){
            $res = $fav_info->delete();
            return ['operation' => 'del'];
        }

        $fav_model->user_id = $user_info->id;
        $fav_model->out_id = $out_id;
        $fav_model->is_type = request()->type;
        $fav_model->save();
        return ['operation' => 'add'];

    }

    // 删除
    public function delFav($out_id){
        if(!in_array(request()->type,[Constant::FAVORITES_TYPE_GOODS, Constant::FAVORITES_TYPE_STORE])){
            throw new RequestException(CodeResponse::VALIDATION_ERROR);
        }

        $user_service = new UserService;
        $user_info = $user_service->getUserInfo();

        $fav_model = new Favorite();
        $fav_model->whereIn('out_id',$out_id)->where(['user_id'=>$user_info['id'],'is_type'=>request()->is_type])->delete();
        return $this->format([],__('base.success'));
    }

    // 判断是否有收藏
    public function isFav($out_id){
        if(!in_array(request()->type,[Constant::FAVORITES_TYPE_GOODS, Constant::FAVORITES_TYPE_STORE])){
            throw new RequestException(CodeResponse::VALIDATION_ERROR);
        }

        $user_service = new UserService;
        $user_info = $user_service->getUserInfo();

        $fav_model = new Favorite();
        $fav_info = $fav_model->where(['user_id' => $user_info['id'], 'out_id' => $out_id, 'is_type' => request()->type])->first();
        if(empty($fav_info)){
            return false;
        }
        return true;
    }
    
}
