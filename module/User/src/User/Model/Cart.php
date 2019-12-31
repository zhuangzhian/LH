<?php
/**
 * DBShop 电子商务系统
 *
 * ==========================================================================
 * @link      http://www.dbshop.net/
 * @copyright Copyright (c) 2012-2018 DBShop.net Inc. (http://www.dbshop.net)
 * @license   http://www.dbshop.net/license.html License
 * ==========================================================================
 *
 * @author    静静的风
 *
 */

namespace User\Model;

class Cart
{
    private static $dataArray = array();

    private static function checkData (array $data)
    {
        self::$dataArray['goods_key']           = (isset($data['goods_key'])            and !empty($data['goods_key']))         ? trim($data['goods_key'])          : null;
        self::$dataArray['goods_id']            = (isset($data['goods_id'])             and !empty($data['goods_id']))          ? intval($data['goods_id'])         : null;
        self::$dataArray['goods_name']          = (isset($data['goods_name'])           and !empty($data['goods_name']))        ? trim($data['goods_name'])         : null;
        self::$dataArray['class_id']            = (isset($data['class_id'])             and !empty($data['class_id']))          ? intval($data['class_id'])         : null;
        self::$dataArray['goods_image']         = (isset($data['goods_image'])          and !empty($data['goods_image']))       ? trim($data['goods_image'])        : null;
        self::$dataArray['goods_stock']         = (isset($data['goods_stock'])          and !empty($data['goods_stock']))       ? intval($data['goods_stock'])      : null;
        self::$dataArray['buy_num']             = (isset($data['buy_num'])              and !empty($data['buy_num']))           ? intval($data['buy_num'])          : null;
        self::$dataArray['goods_type']          = (isset($data['goods_type'])           and !empty($data['goods_type']))        ? intval($data['goods_type'])       : 1;
        self::$dataArray['goods_shop_price']    = (isset($data['goods_shop_price'])     and !empty($data['goods_shop_price']))  ? trim($data['goods_shop_price'])   : '';
        self::$dataArray['buy_min_num']         = (isset($data['buy_min_num'])          and !empty($data['buy_min_num']))       ? intval($data['buy_min_num'])      : 0;
        self::$dataArray['buy_max_num']         = (isset($data['buy_max_num'])          and !empty($data['buy_max_num']))       ? intval($data['buy_max_num'])      : 0;
        self::$dataArray['integral_num']        = (isset($data['integral_num'])         and !empty($data['integral_num']))      ? intval($data['integral_num'])     : 0;
        self::$dataArray['user_unionid']        = (isset($data['user_unionid'])         and !empty($data['user_unionid']))      ? trim($data['user_unionid'])       : '';
        self::$dataArray['user_id']             = (isset($data['user_id'])              and !empty($data['user_id']))           ? intval($data['user_id'])          : 0;

        self::$dataArray = array_filter(self::$dataArray);

        self::$dataArray['goods_stock_state']   = (isset($data['goods_stock_state'])    and !empty($data['goods_stock_state'])) ? intval($data['goods_stock_state']): 0;
        self::$dataArray['goods_color']         = (isset($data['goods_color'])          and !empty($data['goods_color']))       ? trim($data['goods_color'])        : '';
        self::$dataArray['goods_size']          = (isset($data['goods_size'])           and !empty($data['goods_size']))        ? trim($data['goods_size'])         : '';
        self::$dataArray['goods_adv_tag_id']    = (isset($data['goods_adv_tag_id'])     and !empty($data['goods_adv_tag_id']))  ? trim($data['goods_adv_tag_id'])   : '';
        self::$dataArray['goods_adv_tag_name']  = (isset($data['goods_adv_tag_name'])   and !empty($data['goods_adv_tag_name']))? trim($data['goods_adv_tag_name']) : '';
        self::$dataArray['goods_color_name']    = (isset($data['goods_color_name'])     and !empty($data['goods_color_name']))  ? trim($data['goods_color_name'])   : '';
        self::$dataArray['goods_size_name']     = (isset($data['goods_size_name'])      and !empty($data['goods_size_name']))   ? trim($data['goods_size_name'])    : '';
        self::$dataArray['goods_item']          = (isset($data['goods_item'])           and !empty($data['goods_item']))        ? trim($data['goods_item'])         : '';
        self::$dataArray['goods_weight']        = (isset($data['goods_weight'])         and !empty($data['goods_weight']))      ? intval($data['goods_weight'])     : 0;
        self::$dataArray['brand_id']            = (isset($data['brand_id'])             and !empty($data['brand_id']))          ? intval($data['brand_id'])         : null;
        self::$dataArray['class_id_array']      = serialize($data['class_id_array']);

        return self::$dataArray;
    }
    /**
     * 添加购物车过滤
     * @param array $data
     * @return multitype
     */
    public static function addCartData(array $data)
    {
        $data = self::checkData($data);

        return $data;
    }
}