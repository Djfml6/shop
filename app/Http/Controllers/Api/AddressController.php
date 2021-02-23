<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Services\AddressService;
use App\Services\UserService;
use Illuminate\Http\Request;
use App\Http\Requests\Api\AddressRequest;
use App\Http\CodeResponse;

class AddressController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(UserService $user_service)
    {
        return $this->success($user_service->getUserInfo()->address);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AddressRequest $request, UserService $user_service)
    {

        $user = $user_service->getUserInfo();
        $info = $user->address()->create($request->only([
            'consignee', 'country_id', 'province_id', 'city_id', 'district_id', 'address_detail', 'mobile', 'area_info', 'is_default']));
        if(!$info)
        {
            return $this->fail();
        }
        if($info->is_default == true)
        {
            $address_model = new Address;
            $address_model->where('user_id', $user->id)->where('id', '!=', $info->id)->update(['is_default' => false]);
        }
        return $this->success([]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(UserService $user_service, $id)
    {
        $info = $user_service->getUserInfo()->address()->where('id', $id)->with(['province', 'city', 'district'])->first();
        return $this->success($info);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(AddressRequest $request, UserService $user_service, $id)
    {
        $address = Address::query()->find($id);
        $this->authorize('own', $address);
        $info = $address->update($request->only([
            'consignee', 'country_id', 'province_id', 'city_id', 'district_id', 'address_detail', 'mobile', 'area_info', 'is_default'
        ]));
        if($request->is_default == true)
        {
            $address_model = new Address;
            $address_model->where('user_id', $user_service->getUserInfo()->id)->where('id', '!=', $id)->update(['is_default' => false]);
        }
        return $info ? $this->success() : $this->fail();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $address = Address::query()->find($id);
        $this->authorize('own', $address);
        $info = $address->delete();
        return $info? $this->success() : $this->fail();
    }

    // 设置默认地址
    public function set_default(Request $request, UserService $user_service){
        $address_model = new Address();
        $user = $user_service->getUserInfo();
        $address_info = $user->address()->where('id',$request->id)->first();
        if(!$address_info)
        {
            return $this->fail();
        }

        $address_model->where('user_id',$user->id)->update(['is_default'=>0]);
        $address_info = $address_model->where('id',$request->id)->first();
        $address_info->is_default = 1;
        $address_info->save();
        return $this->success();
    }

    // 获取默认地址如果没有设置默认地址则取靠前的第一条
    public function get_default(Request $request, UserService $user_service){
        $address_model = $user_service->getUserInfo()->address();
        $address = $address_model->where('is_default' , true)->with(['province', 'city', 'district'])->first();
        if(!$address)
        {
            $address = $user_service->getUserInfo()->address()->with(['province', 'city', 'district'])->first();
        }
        return $this->success($address);
    }
}
