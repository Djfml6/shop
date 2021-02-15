<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FavoriteService;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(FavoriteService $fav_service, Request $request)
    {
        $info = $fav_service->getFav();
        return $this->success($info);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request,FavoriteService $fav_service)
    {
        $info = $fav_service->addOrDelFav($request->id);
        return $this->success($info);
    }

    /**
     * 当前商品或者店铺是否在收藏
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(FavoriteService $fav_service, $id)
    {
        $info = $fav_service->isFav($id);
        return $this->success($info);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(FavoriteService $fav_service,$id)
    {
        $idArray = array_filter(explode(',',$id),function($item){
            return is_numeric($item);
        });

        $rs = $fav_service->delFav($idArray);
        return $rs['status']?$this->success($rs['data'],$rs['msg']):$this->error($rs['msg']);
    }
}
