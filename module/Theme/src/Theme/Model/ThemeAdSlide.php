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


class ThemeAdSlide
{
    private static $dataArray = array();

    private static function checkData(array $data)
    {
        self::$dataArray['theme_ad_id'] = (isset($data['theme_ad_id']) and !empty($data['theme_ad_id']))
            ? intval($data['theme_ad_id'])
            : null;

        self::$dataArray['theme_ad_slide_info'] = (isset($data['theme_ad_slide_info']) and !empty($data['theme_ad_slide_info']))
            ? trim($data['theme_ad_slide_info'])
            : null;

        self::$dataArray['theme_ad_slide_image'] = (isset($data['theme_ad_slide_image']) and !empty($data['theme_ad_slide_image']))
            ? trim($data['theme_ad_slide_image'])
            : null;

        self::$dataArray['theme_ad_slide_sort'] = (isset($data['theme_ad_slide_sort']) and !empty($data['theme_ad_slide_sort']))
            ? intval($data['theme_ad_slide_sort'])
            : null;

        self::$dataArray['theme_ad_slide_url'] = (isset($data['theme_ad_slide_url']) and !empty($data['theme_ad_slide_url']))
            ? trim($data['theme_ad_slide_url'])
            : null;

        self::$dataArray['item_id'] = (isset($data['item_id']) and !empty($data['item_id']))
            ? intval($data['item_id'])
            : null;

        self::$dataArray = array_filter(self::$dataArray);

        return self::$dataArray;
    }
    /**
     * 添加专题项目广告幻灯片过滤
     * @param array $data
     * @return array
     */
    public static function addThemeAdSlideData (array $data)
    {
        return self::checkData($data);
    }
}