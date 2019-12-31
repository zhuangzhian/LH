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

namespace User\Model;

/**
 * 会员优惠券过滤表
 * Class UserCoupon
 * @package User\Model
 */
class UserCoupon
{
    private static $dataArray = array();

    private static function checkData (array $data)
    {
        self::$dataArray['user_coupon_id'] = (isset($data['user_coupon_id']) and !empty($data['user_coupon_id']))
            ? intval($data['user_coupon_id'])
            : null;

        self::$dataArray['coupon_use_state'] = (isset($data['coupon_use_state']) and !empty($data['coupon_use_state']))
            ? trim($data['coupon_use_state'])
            : null;

        self::$dataArray['user_id'] = (isset($data['user_id']) and !empty($data['user_id']))
            ? intval($data['user_id'])
            : null;

        self::$dataArray['user_name'] = (isset($data['user_name']) and !empty($data['user_name']))
            ? trim($data['user_name'])
            : null;

        self::$dataArray['used_order_id'] = (isset($data['used_order_id']) and !empty($data['used_order_id']))
            ? intval($data['used_order_id'])
            : null;

        self::$dataArray['used_order_sn'] = (isset($data['used_order_sn']) and !empty($data['used_order_sn']))
            ? trim($data['used_order_sn'])
            : null;

        self::$dataArray['used_time'] = (isset($data['used_time']) and !empty($data['used_time']))
            ? intval($data['used_time'])
            : null;

        self::$dataArray['get_time'] = (isset($data['get_time']) and !empty($data['get_time']))
            ? intval($data['get_time'])
            : null;

        self::$dataArray['coupon_id'] = (isset($data['coupon_id']) and !empty($data['coupon_id']))
            ? intval($data['coupon_id'])
            : null;

        self::$dataArray['coupon_name'] = (isset($data['coupon_name']) and !empty($data['coupon_name']))
            ? trim($data['coupon_name'])
            : null;

        self::$dataArray = array_filter(self::$dataArray);

        self::$dataArray['get_order_id'] = (isset($data['get_order_id']) and !empty($data['get_order_id']))
            ? intval($data['get_order_id'])
            : null;

        self::$dataArray['get_order_sn'] = (isset($data['get_order_sn']) and !empty($data['get_order_sn']))
            ? trim($data['get_order_sn'])
            : null;

        self::$dataArray['coupon_info'] = (isset($data['coupon_info']) and !empty($data['coupon_info']))
            ? trim($data['coupon_info'])
            : null;

        self::$dataArray['coupon_start_use_time'] = (isset($data['coupon_start_use_time']) and !empty($data['coupon_start_use_time']))
            ? trim($data['coupon_start_use_time'])
            : null;

        self::$dataArray['coupon_expire_time'] = (isset($data['coupon_expire_time']) and !empty($data['coupon_expire_time']))
            ? trim($data['coupon_expire_time'])
            : null;

        return self::$dataArray;
    }
    /**
     *添加会员优惠券过滤
     * @param array $data
     * @return array
     */
    public static function addUserCouponData (array $data)
    {
        return self::checkData($data);
    }
}