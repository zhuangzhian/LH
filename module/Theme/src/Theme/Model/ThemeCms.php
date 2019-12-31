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


class ThemeCms
{
    private static $dataArray = array();

    private static function checkData(array $data)
    {
        self::$dataArray['theme_cms_id'] = (isset($data['theme_cms_id']) and !empty($data['theme_cms_id']))
            ? intval($data['theme_cms_id'])
            : null;

        self::$dataArray['cms_id'] = (isset($data['cms_id']) and !empty($data['cms_id']))
            ? intval($data['cms_id'])
            : null;

        self::$dataArray['theme_cms_sort'] = (isset($data['theme_cms_sort']) and !empty($data['theme_cms_sort']))
            ? intval($data['theme_cms_sort'])
            : null;

        self::$dataArray['item_id'] = (isset($data['item_id']) and !empty($data['item_id']))
            ? intval($data['item_id'])
            : null;

        self::$dataArray = array_filter(self::$dataArray);

        return self::$dataArray;
    }
    /**
     * 添加专题项目文章过滤
     * @param array $data
     * @return array
     */
    public static function addThemeCmsData (array $data)
    {
        return self::checkData($data);
    }
}