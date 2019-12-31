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

class FrontSide
{
    private static $dataArray = array();

    private static function checkData (array $data)
    {
        self::$dataArray['frontside_id'] = (isset($data['frontside_id']) and !empty($data['frontside_id']))
            ? intval($data['frontside_id'])
            : null;

        self::$dataArray['frontside_name'] = (isset($data['frontside_name']) and !empty($data['frontside_name']))
            ? trim($data['frontside_name'])
            : null;

        self::$dataArray['frontside_sort'] = (isset($data['frontside_sort']) and !empty($data['frontside_sort']))
            ? intval($data['frontside_sort'])
            : null;

        self::$dataArray = array_filter(self::$dataArray);

        self::$dataArray['frontside_url'] = (isset($data['frontside_url']) and !empty($data['frontside_url']))
            ? trim($data['frontside_url'])
            : '';

        self::$dataArray['frontside_topid'] = (isset($data['frontside_topid']) and !empty($data['frontside_topid']))
            ? intval($data['frontside_topid'])
            : 0;

        self::$dataArray['frontside_class_id'] = (isset($data['frontside_class_id']) and !empty($data['frontside_class_id']))
            ? intval($data['frontside_class_id'])
            : 0;

        self::$dataArray['frontside_new_window'] = (isset($data['frontside_new_window']) and !empty($data['frontside_new_window']))
            ? intval($data['frontside_new_window'])
            : 0;

        return self::$dataArray;
    }
    public static function addFrontSideData (array $data)
    {
        return self::checkData($data);
    }
    public static function updateFrontSideData (array $data)
    {
        unset($data['frontside_id']);
        return self::checkData($data);
    }
}