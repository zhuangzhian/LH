<?php
/**
 * DBShop 电子商务系统
 *
 * ==========================================================================
 * @link      http://www.dbshop.net/
 * @copyright Copyright (c) 2012-2015 DBShop.net Inc. (http://www.dbshop.net)
 * @license   http://www.dbshop.net/license.html License
 * ==========================================================================
 *
 * @author    静静的风
 *
 */

namespace Goods\Model;

/**
 * 商品标签
 */
class GoodsTag
{
    private static $dataArray = array();
    
    private static function checkData (array $data)
    {
        self::$dataArray['tag_id']       = (isset($data['tag_id'])       and !empty($data['tag_id']))        ? intval($data['tag_id'])       : null;
        self::$dataArray['tag_sort']     = (isset($data['tag_sort'])     and !empty($data['tag_sort']))      ? intval($data['tag_sort'])     : 255;
        self::$dataArray = array_filter(self::$dataArray);
        self::$dataArray['tag_str']      = (isset($data['tag_str'])      and !empty($data['tag_str']))       ? trim($data['tag_str'])        : '';
        self::$dataArray['tag_group_id'] = (isset($data['tag_group_id']) and !empty($data['tag_group_id']))  ? intval($data['tag_group_id']) : 0;
        self::$dataArray['tag_type']     = (isset($data['tag_type'])     and !empty($data['tag_type']))      ? trim($data['tag_type'])       : null;
        self::$dataArray['template_tag'] = (isset($data['template_tag']) and !empty($data['template_tag']))  ? trim($data['template_tag'])   : '';
        self::$dataArray['show_type']    = (isset($data['show_type'])    and !empty($data['show_type']))     ? trim($data['show_type'])      : 'pc';

        return self::$dataArray;
    }
    /**
     * 添加标签过滤
     * @param array $data
     * @return array
     */
    public static function addGoodsTagData (array $data)
    {
        return self::checkData($data);
    }
    /**
     * 编辑更新标签过滤
     * @param array $data
     * @return array
     */
    public static function updateGoodsTagData (array $data)
    {
        if(isset($data['tag_id'])) unset($data['tag_id']);
        $data = self::checkData($data);
        if(isset($data['show_type'])) unset($data['show_type']);

        return $data;
    }
}

?>