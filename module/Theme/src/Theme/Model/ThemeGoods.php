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

namespace Theme\Model;


class ThemeGoods
{
    private static $dataArray = array();

    private static function checkData (array $data)
    {
        self::$dataArray['theme_goods_id'] = (isset($data['theme_goods_id']) and !empty($data['theme_goods_id']))
            ? intval($data['theme_goods_id'])
            : null;

        self::$dataArray['goods_id'] = (isset($data['goods_id']) and !empty($data['goods_id']))
            ? intval($data['goods_id'])
            : null;

        self::$dataArray['goods_sort'] = (isset($data['goods_sort']) and !empty($data['goods_sort']))
            ? intval($data['goods_sort'])
            : null;

        self::$dataArray['item_id'] = (isset($data['item_id']) and !empty($data['item_id']))
            ? intval($data['item_id'])
            : null;

        self::$dataArray = array_filter(self::$dataArray);

        return self::$dataArray;
    }
    /**
     * 添加专题项目商品过滤
     * @param array $data
     * @return array
     */
    public static function addThemeGoodsData (array $data)
    {
        return self::checkData($data);
    }
}