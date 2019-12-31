<?php
/**
 * DBShop 电子商务系统
 *
 * ==========================================================================
 * @link      http://www.dbshop.net/
 * @copyright Copyright (c) 2012-2016 DBShop.net Inc. (http://www.dbshop.net)
 * @license   http://www.dbshop.net/license.html License
 * ==========================================================================
 *
 * @author    静静的风
 *
 */

namespace Plugin\Model;

class Pluginortemplate
{
    private static $dataArray = array();

    private static function checkData (array $data)
    {
        self::$dataArray['update_id'] = (isset($data['plugin_id']) and !empty($data['plugin_id']))
            ? intval($data['plugin_id'])
            : null;

        self::$dataArray['update_code'] = (isset($data['update_code']) and !empty($data['update_code']))
            ? trim($data['update_code'])
            : null;

        self::$dataArray['update_type'] = (isset($data['update_type']) and !empty($data['update_type']))
            ? trim($data['update_type'])
            : null;

        self::$dataArray['update_str'] = (isset($data['update_str']) and !empty($data['update_str']))
            ? trim($data['update_str'])
            : null;

        return array_filter(self::$dataArray);
    }

    public static function addPluginortemplateData($data)
    {
        return self::checkData($data);
    }
}