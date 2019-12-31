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

namespace User\Model;


class UserRegExtendField
{
    private static $dataArray = array();
    /**
     *
     * @param array $data
     * @return multitype:
     */
    private static function checkData (array $data)
    {
        self::$dataArray['field_id'] = (isset($data['field_id']) and !empty($data['field_id']))
            ? intval($data['field_id'])
            : null;

        self::$dataArray['field_name'] = (isset($data['field_name']) and !empty($data['field_name']))
            ? trim($data['field_name'])
            : null;

        self::$dataArray['field_title'] = (isset($data['field_title']) and !empty($data['field_title']))
            ? trim($data['field_title'])
            : null;

        self::$dataArray['field_unit'] = (isset($data['field_unit']) and !empty($data['field_unit']))
            ? trim($data['field_unit'])
            : null;

        self::$dataArray['field_info'] = (isset($data['field_info']) and !empty($data['field_info']))
            ? trim($data['field_info'])
            : null;

        self::$dataArray['field_radio_checkbox_select'] = (isset($data['field_radio_checkbox_select']) and !empty($data['field_radio_checkbox_select']))
            ? trim($data['field_radio_checkbox_select'])
            : null;

        self::$dataArray['field_type'] = (isset($data['field_type']) and !empty($data['field_type']))
            ? trim($data['field_type'])
            : null;

        self::$dataArray['field_sort'] = (isset($data['field_sort']) and !empty($data['field_sort']))
            ? intval($data['field_sort'])
            : 255;

        self::$dataArray['field_null'] = (isset($data['field_null']) and !empty($data['field_null']))
            ? intval($data['field_null'])
            : null;

        self::$dataArray['field_state'] = (isset($data['field_state']) and !empty($data['field_state']))
            ? intval($data['field_state'])
            : null;

        self::$dataArray['field_input_length'] = (isset($data['field_input_length']) and !empty($data['field_input_length']))
            ? trim($data['field_input_length'])
            : null;

        self::$dataArray['field_input_max'] = (isset($data['field_input_max']) and !empty($data['field_input_max']))
            ? trim($data['field_input_max'])
            : null;

        self::$dataArray['field_textarea_height'] = (isset($data['field_textarea_height']) and !empty($data['field_textarea_height']))
            ? trim($data['field_textarea_height'])
            : null;

        self::$dataArray = array_filter(self::$dataArray);

        return self::$dataArray;
    }
    /**
     * 添加会员扩展字段过滤
     * @param array $data
     * @return multitype
     */
    public static function addUserRegExtendFieldData(array $data)
    {
        return self::checkData($data);
    }
}