<?php
namespace App\Http;

/**
 *
 */
class CodeResponse
{

	// 业务操作码 2xx表示成功 4xx客户端错误码 5xx服务端错误码,后拼接 3 位
	// 通用返回码

	const SUCESS = [200, '操作成功'];
	const FAIL = [-1, '操作失败'];
	const LOGIN_INVALID = [-101, '登录失败'];

	const TOKEN_INVALID = [400001, 'token无效'];
	const VALIDATION_ERROR = [400101, '参数错误'];
	const REGISTER_CAPTCHA_ERROR = [400102, '验证码超时'];
	const NO_GATEWAY_AVAILABLE = [400103, '短信发送异常'];
    const USER_INVALID = [400105, '账号密码不对'];
    const REGISTER_INVALID = [400105, '注册异常'];
    const REQUEST_INVALID = [400107, '请求参数不合法'];
    const ACTION_UNAUTHORIZED = [400108, '没有操作权限'];
    const GOODS_INVALID = [400109, '商品不存在'];
    const STORE_INVALID = [400509, '店铺不存在'];
    const GOODS_SKU_INVALID = [400510, '请选择规格'];
    const CART_ADD_FAIL = [400511, '添加购物车失败'];
    const COUPON_INVALID = [400512, '优惠券不存在'];
    const GOODS_STOCK_INVALID = [400513, '库存不足'];
    const ORDER_INVALID = [400515, '订单不存在'];

}
