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


class ThemeItem
{
    private static $dataArray = array();

    private static function checkData (array $data)
    {
        self::$dataArray['item_id'] = (isset($data['item_id']) and !empty($data['item_id']))
            ? intval($data['item_id'])
            : null;

        self::$dataArray['item_title'] = (isset($data['item_title']) and !empty($data['item_title']))
            ? trim($data['item_title'])
            : null;

        self::$dataArray['item_type'] = (isset($data['item_type']) and !empty($data['item_type']))
            ? trim($data['item_type'])
            : null;

        self::$dataArray['item_code'] = (isset($data['item_code']) and !empty($data['item_code']))
            ? trim($data['item_code'])
            : null;

        self::$dataArray['theme_template'] = (isset($data['theme_template']) and !empty($data['theme_template']))
            ? trim($data['theme_template'])
            : null;

        self::$dataArray['theme_id'] = (isset($data['theme_id']) and !empty($data['theme_id']))
            ? intval($data['theme_id'])
            : null;

        self::$dataArray = array_filter(self::$dataArray);

        return self::$dataArray;
    }
    /**
     * 添加专题项目过滤
     * @param array $data
     * @return array
     */
    public static function addThemeItemData (array $data)
    {
        return self::checkData($data);
    }
}