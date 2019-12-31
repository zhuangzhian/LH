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

use Zend\Db\TableGateway\AbstractTableGateway;
use \Zend\Db\Adapter\AdapterAwareInterface;
use Zend\Db\Adapter\Adapter;
use Theme\Model\ThemeAd as dbshopCheckInData;

class ThemeAdTable extends AbstractTableGateway implements AdapterAwareInterface
{
    protected $table = 'dbshop_theme_ad';

    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加广告
     * @param array $data
     * @return int|null
     */
    public function addAd(array $data)
    {
        $row = $this->insert(dbshopCheckInData::addThemeAdData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 更新广告
     * @param array $data
     * @param array $where
     * @return int
     */
    public function updateAd(array $data, array $where)
    {
        return $this->update(dbshopCheckInData::updateAdDate($data), $where);
    }
    /**
     * 获取广告信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoAd(array $where)
    {
        $result = $this->select($where);
        if($result) {
            $array = $result->toArray();
            if(isset($array[0])) return $array[0];
        }
        return null;
    }
    /**
     * 获取广告信息数组
     * @param array $wehre
     * @return array|null
     */
    public function listAd(array $wehre)
    {
        $result = $this->select($wehre);
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /**
     * 删除广告
     * @param array $where
     * @return null
     */
    public function delAd(array $where)
    {
        $info = $this->infoAd($where);
        if(!empty($info)) {
            if($info->theme_ad_type == 'image' and !empty($info->theme_ad_body)) @unlink(DBSHOP_PATH . $info->theme_ad_body);
            return $this->delete($where);
        }
        return null;
    }
}