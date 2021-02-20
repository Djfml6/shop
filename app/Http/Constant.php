<?php
namespace App\Http;

class Constant
{
    //收藏类型
    const COLLECT_TYPE_GOODS = 0;
    const COLLECT_TYPE_TOPIC = 1;

    // Money_log
    const MONEY_TYPE_BALANCE = 0; //余额
    const MONEY_TYPE_FROZEN = 1; //冻结
    const MONEY_TYPE_INTEGRAL = 2; //积分

    //store
    const STORE_TYPE_SELF = 0; 


    // group_log
    const GROUPLOG_STATUS_CACEL = 0; //拼团状态取消
    const GROUPLOG_STATUS_SUCCESS = 1; //拼团状态完成
    const GROUPLOG_STATUS_ING = 2; //拼团状态进行中


    // comment
    const COMMENT_TYPE_GOODS = 1; //好评
    const COMMENT_TYPE_MIDDLE = 2; //中评
    const COMMENT_TYPE_BAD = 3; //差评
    const COMMENT_TYPE_IMG = 4; //晒图

    // orders
    const ORDER_STATUS_CANCLE = 0; //取消
    const ORDER_STATUS_WAITPAY = 1; //等待支付
    const ORDER_STATUS_WAITREC = 2; // 等待发货
    const ORDER_STATUS_CONFIRM = 3; //确认收货
    const ORDER_STATUS_WAITCOMMENT = 4; //等待评论
    const ORDER_STATUS_SERVICE = 5; //售后
    const ORDER_STATUS_COMPLETE = 6; //订单完成
    const ORDER_REFUND_REFUND = 0;//退款
    const ORDER_REFUND_RETURN_GOODS = 1;//退货
    const ORDER_REFUND_END = 2;//处理结束

    // favorites
    const FAVORITES_TYPE_GOODS = 0;
    const FAVORITES_TYPE_STORE = 1;

    // cart
    const CART_TOOL_DELETE = 0; //数量减少
    const CART_TOOL_ADD = 1; //数量增加

    // coupon
    const COUPON_TYPE_FIXED = 0; //满减券
    const COUPON_TYPE_PERCENT = 1; //折扣券
    const COUPON_TYPE_SILL = 2; //无门槛券
    const COUPON_USE_TIME_TYPE_BETWEEN = 0; //有效期XXX至XXX
    const COUPON_USE_TIME_TYPE_EXPIRES = 1; //领券当日起x天内有效
    const COUPON_USE_TIME_TYPE_NEXTEXPIRES = 2; //领券次日起x天内有效
    const COUPON_STATUS_NORMAL = 0; //正常
    const COUPON_STATUS_END = 1; //已发完
    const COUPON_RANGE_ALL = 0; //使用范围（全场商品）
    const COUPON_RANGE_PART = 1; //使用范围（部分商品）
    const COUPON_USER_NOTLIMIT = 0; //用户限制 0:所有人可领取
    const COUPON_USER_LIMIT = [1, 2, 3]; //用户限制 1普通用户 2初级会员 3中级会员




    // coupon_log
    const COUPON_STATUS_NOT_USED = 0; //未使用
    const COUPON_STATUS_USED = 1; //已使用
    const COUPON_GET_ACTIVE = 1; //获取方式（主动领取）
    const COUPON_GET_GIVE = 2; //获取方式（系统赠送）

    // shipping
    const SHIPPING_NOT_FREE = 0; //不包邮
    const SHIPPING_FREE = 1; //包邮

    const SHIPPING_PRICE_METHOD_WEIGHT = 1; //计价方式：重量
    const SHIPPING_PRICE_METHOD_NUM = 2; //计价方式：件数
    const SHIPPING_PRICE_METHOD_VOLUME = 3; //计价方式：体积

    const SHIPPING_CONDITION_NOT_FREE = 0; //是否指定条件包邮：否
    const SHIPPING_CONDITION_FREE = 1; //是否指定条件包邮：是
}
