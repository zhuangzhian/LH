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

class GoodsAdvSpecGroup
{
    private static $dataArray = array();

    private static function checkData ($data)
    {
        self::$dataArray['goods_id'] = (isset($data['goods_id']) and !empty($data['goods_id']))
            ? intval($data['goods_id'])
            : null;

        self::$dataArray['group_id'] = (isset($data['group_id']) and !empty($data['group_id']))
            ? intval($data['group_id'])
            : null;

        self::$dataArray = array_filter(self::$dataArray);

        self::$dataArray['selected_tag_id'] = (isset($data['selected_tag_id']) and !empty($data['selected_tag_id']))
            ? trim($data['selected_tag_id'])
            : '';

        return self::$dataArray;
    }
    /**
     * 过滤添加商品规格组基本信息
     * @param array $data
     * @return array
     */
    public static function addGoodsAdvSpecGroupData (array $data)
    {
        return self::checkData($data);
    }
    /**
     * 过滤更新商品规格组基本信息
     * @param array $data
     * @return array
     */
    public static function updateGoodsAdvSpecGroupData(array $data)
    {
        return self::checkData($data);
    }
}