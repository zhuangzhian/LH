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
 * 会员组价格
 * Class GoodsUsergroupPrice
 * @package Goods\Model
 */
class GoodsUsergroupPrice
{
    private static $dataArray = array();

    private static function checkData (array $data)
    {
        self::$dataArray['goods_id'] = (isset($data['goods_id']) and !empty($data['goods_id']))
            ? intval($data['goods_id'])
            : null;

        self::$dataArray['user_group_id'] = (isset($data['user_group_id']) and !empty($data['user_group_id']))
            ? intval($data['user_group_id'])
            : null;

        self::$dataArray['adv_spec_tag_id'] = (isset($data['adv_spec_tag_id']) and !empty($data['adv_spec_tag_id']))
            ? trim($data['adv_spec_tag_id'])
            : null;

        self::$dataArray = array_filter(self::$dataArray);

        self::$dataArray['goods_user_group_price'] = (isset($data['goods_user_group_price']) and !empty($data['goods_user_group_price']))
            ? trim($data['goods_user_group_price'])
            : 0;

        self::$dataArray['goods_color'] = (isset($data['goods_color']) and !empty($data['goods_color']))
            ? trim($data['goods_color'])
            : '';

        self::$dataArray['goods_size'] = (isset($data['goods_size']) and !empty($data['goods_size']))
            ? trim($data['goods_size'])
            : '';

        return self::$dataArray;
    }
    /**
     * 客户组价格添加过滤
     * @param array $data
     * @return array
     */
    public static function addGoodsUserGroupPriceData(array $data)
    {
        return self::checkData($data);
    }
    /**
     * 客户组价格更新过滤
     * @param array $data
     * @return array
     */
    public static function updateGoodsGroupPriceData(array $data)
    {
        unset($data['goods_id']);

        return self::checkData($data);
    }
}