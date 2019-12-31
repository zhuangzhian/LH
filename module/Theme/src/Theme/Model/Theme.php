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


class Theme
{
    private static $dataArray = array();

    private static function checkData (array $data)
    {
        self::$dataArray['theme_id'] = (isset($data['theme_id']) and !empty($data['theme_id']))
            ? intval($data['theme_id'])
            : null;

        self::$dataArray['theme_name'] = (isset($data['theme_name']) and !empty($data['theme_name']))
            ? trim($data['theme_name'])
            : null;

        self::$dataArray['theme_sign'] = (isset($data['theme_sign']) and !empty($data['theme_sign']))
            ? trim($data['theme_sign'])
            : null;

        self::$dataArray['theme_template'] = (isset($data['theme_template']) and !empty($data['theme_template']))
            ? trim($data['theme_template'])
            : null;

        self::$dataArray['theme_state'] = (isset($data['theme_state']) and !empty($data['theme_state']))
            ? intval($data['theme_state'])
            : 1;

        self::$dataArray['theme_extend_name'] = (isset($data['theme_extend_name']) and !empty($data['theme_extend_name']))
            ? trim($data['theme_extend_name'])
            : null;

        self::$dataArray['theme_keywords'] = (isset($data['theme_keywords']) and !empty($data['theme_keywords']))
            ? trim($data['theme_keywords'])
            : null;

        self::$dataArray['theme_description'] = (isset($data['theme_description']) and !empty($data['theme_description']))
            ? trim($data['theme_description'])
            : null;

        self::$dataArray = array_filter(self::$dataArray);

        return self::$dataArray;
    }
    /**
     * 添加专题过滤
     * @param array $data
     * @return array
     */
    public static function addThemeData (array $data)
    {
        return self::checkData($data);
    }
}