<?php
/**
 * DBShop 电子商务系统
 *
 * ==========================================================================
 * @link      http://www.dbshop.net/
 * @copyright Copyright (c) 2012-2017 DBShop.net Inc. (http://www.dbshop.net)
 * @license   http://www.dbshop.net/license.html License
 * ==========================================================================
 *
 * @author    静静的风
 *
 */

namespace Goods\Model;

/**
 * 优惠券信息过滤
 */
class Coupon
{
    private static $dataArray = array();

    private static function checkData(array $data)
    {
        self::$dataArray['coupon_id'] = (isset($data['coupon_id']) and !empty($data['coupon_id']))
            ? intval($data['coupon_id'])
            : null;

        self::$dataArray['coupon_name'] = (isset($data['coupon_name']) and !empty($data['coupon_name']))
            ? trim($data['coupon_name'])
            : null;

        self::$dataArray['coupon_state'] = (isset($data['coupon_state']) and !empty($data['coupon_state']))
            ? intval($data['coupon_state'])
            : null;

        self::$dataArray['get_coupon_type'] = (isset($data['get_coupon_type']) and !empty($data['get_coupon_type']))
            ? trim($data['get_coupon_type'])
            : null;

        self::$dataArray['get_user_type'] = (isset($data['get_user_type']) and !empty($data['get_user_type']))
            ? trim($data['get_user_type'])
            : null;

        self::$dataArray['get_goods_type'] = (isset($data['get_goods_type']) and !empty($data['get_goods_type']))
            ? trim($data['get_goods_type'])
            : null;

        self::$dataArray['coupon_goods_type'] = (isset($data['coupon_goods_type']) and !empty($data['coupon_goods_type']))
            ? trim($data['coupon_goods_type'])
            : null;

        self::$dataArray = array_filter(self::$dataArray);

        self::$dataArray['coupon_info'] = (isset($data['coupon_info']) and !empty($data['coupon_info']))
            ? trim($data['coupon_info'])
            : null;

        self::$dataArray['shopping_amount'] = (isset($data['shopping_amount']) and !empty($data['shopping_amount']))
            ? intval($data['shopping_amount'])
            : 0;

        self::$dataArray['shopping_discount'] = (isset($data['shopping_discount']) and !empty($data['shopping_discount']))
            ? intval($data['shopping_discount'])
            : 0;

        self::$dataArray['get_shopping_amount'] = (isset($data['get_shopping_amount']) and !empty($data['get_shopping_amount']))
            ? (intval($data['get_shopping_amount']) < 0 ? 0 : intval($data['get_shopping_amount']))
            : 0;

        self::$dataArray['get_coupon_start_time'] = (isset($data['get_coupon_start_time']) and !empty($data['get_coupon_start_time']))
            ? strtotime($data['get_coupon_start_time'])
            : null;

        self::$dataArray['get_coupon_end_time'] = (isset($data['get_coupon_end_time']) and !empty($data['get_coupon_end_time']))
            ? strtotime($data['get_coupon_end_time'])
            : null;

        self::$dataArray['coupon_start_time'] = (isset($data['coupon_start_time']) and !empty($data['coupon_start_time']))
            ? strtotime($data['coupon_start_time'])
            : null;

        self::$dataArray['coupon_end_time'] = (isset($data['coupon_end_time']) and !empty($data['coupon_end_time']))
            ? strtotime($data['coupon_end_time'])
            : null;

        self::$dataArray['get_user_group'] = (isset($data['get_user_group']) and !empty($data['get_user_group']))
            ? trim($data['get_user_group'])
            : '';

        self::$dataArray['get_coupon_goods_body'] = (isset($data['get_coupon_goods_body']) and !empty($data['get_coupon_goods_body']))
            ? trim($data['get_coupon_goods_body'])
            : '';

        self::$dataArray['coupon_use_channel'] = (isset($data['coupon_use_channel']) and !empty($data['coupon_use_channel']))
            ? serialize($data['coupon_use_channel'])
            : '';

        self::$dataArray['coupon_goods_body'] = (isset($data['coupon_goods_body']) and !empty($data['coupon_goods_body']))
            ? trim($data['coupon_goods_body'])
            : '';

        return self::$dataArray;
    }
    /**
     * 过滤添加优惠规则信息
     * @param array $data
     * @return array
     */
    public static function addCouponData(array $data)
    {
        return self::checkData($data);
    }
    /**
     * 过滤更新优惠规则信息
     * @param array $data
     * @return array
     */
    public static function updateCouponData(array $data)
    {
        unset($data['promotions_id']);
        return self::checkData($data);
    }
}