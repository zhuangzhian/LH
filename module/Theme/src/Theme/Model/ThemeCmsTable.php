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
use Theme\Model\ThemeCms as dbshopCheckInData;

class ThemeCmsTable extends AbstractTableGateway implements AdapterAwareInterface
{
    protected $table = 'dbshop_theme_cms';

    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加cms
     * @param array $data
     * @return int|null
     */
    public function addCms(array $data)
    {
        $row = $this->insert(dbshopCheckInData::addThemeCmsData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 更新cms
     * @param array $data
     * @param array $where
     * @return int
     */
    public function updateCms(array $data, array $where)
    {
        return $this->update($data, $where);
    }
    /**
     * 获取cms信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoCms(array $where)
    {
        $result = $this->select($where);
        if($result) {
            return $result->current();
        }
        return null;
    }
    /**
     * 获取cms信息数组
     * @param array $where
     * @return array|null
     */
    public function listCms(array $where)
    {
        $result = $this->select($where);
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /**
     * 删除cms信息
     * @param array $where
     * @return int
     */
    public function delCms(array $where)
    {
        return $this->delete($where);
    }
}