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


class ThemeAd
{
    private static $dataArray = array();

    private static function checkData(array $data)
    {
        self::$dataArray['theme_ad_id'] = (isset($data['theme_ad_id']) and !empty($data['theme_ad_id']))
            ? intval($data['theme_ad_id'])
            : null;

        self::$dataArray['theme_ad_type'] = (isset($data['theme_ad_type']) and !empty($data['theme_ad_type']))
            ? trim($data['theme_ad_type'])
            : null;

        self::$dataArray['theme_ad_url'] = (isset($data['theme_ad_url']) and !empty($data['theme_ad_url']))
            ? trim($data['theme_ad_url'])
            : null;

        self::$dataArray['theme_ad_body'] = (isset($data['theme_ad_body']) and !empty($data['theme_ad_body']))
            ? trim($data['theme_ad_body'])
            : null;

        self::$dataArray['item_id'] = (isset($data['item_id']) and !empty($data['item_id']))
            ? intval($data['item_id'])
            : null;

        self::$dataArray = array_filter(self::$dataArray);

        return self::$dataArray;
    }
    /**
     * 添加专题项目广告过滤
     * @param array $data
     * @return array
     */
    public static function addThemeAdData (array $data)
    {
        return self::checkData($data);
    }
    /**
     * 更新广告内容
     * @param array $data
     * @return array
     */
    public static function updateAdDate (array $data)
    {
        if(isset($data['theme_ad_id'])) unset($data['theme_ad_id']);

        return self::checkData($data);
    }
}