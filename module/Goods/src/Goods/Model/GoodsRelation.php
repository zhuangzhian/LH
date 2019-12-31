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


class GoodsRelation
{
    private static $dataArray = array();

    private static function checkData (array $data)
    {
        self::$dataArray['relation_id']      = (isset($data['relation_id'])       and !empty($data['relation_id']))       ? intval($data['relation_id'])       : null;
        self::$dataArray['relation_goods_id']= (isset($data['relation_goods_id']) and !empty($data['relation_goods_id'])) ? intval($data['relation_goods_id']) : null;
        self::$dataArray['relation_sort']    = (isset($data['relation_sort'])     and !empty($data['relation_sort']))     ? intval($data['relation_sort'])     : null;
        self::$dataArray = array_filter(self::$dataArray);
        self::$dataArray['goods_id']        = (isset($data['goods_id'])         and !empty($data['goods_id']))         ? intval($data['goods_id'])         : 0;

        return self::$dataArray;
    }
    /**
     * 添加关联商品的过滤
     * @param array $data
     * @return array
     */
    public static function addRelationGoodsData(array $data)
    {
        return self::checkData($data);
    }
}