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

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Goods\Model\FrontSide as dbshopCheckInData;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;

class FrontSideTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_frontside';

    public function setDbAdapter (Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加侧边信息
     * @param array $data
     * @return NULL
     */
    public function addFrontside(array $data)
    {
        $row = $this->insert(dbshopCheckInData::addFrontSideData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 更新侧边信息
     * @param array $data
     * @param array $where
     * @return boolean
     */
    public function updateFrontside(array $data, array $where)
    {
        $update = $this->update(dbshopCheckInData::updateFrontSideData($data), $where);
        return true;
    }
    /**
     * 侧边列表
     * @param array $where
     * @return NULL
     */
    public function listFrontside(array $where=array())
    {
        $result = $this->select(function (Select $select) use ($where) {
            $select->where($where)
                ->order('frontside_sort ASC');
        });

        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /**
     * 获取单个侧边信息
     * @param array $where
     * @return mixed|NULL
     */
    public function infoFrontside (array $where)
    {
        $row = $this->select(function (Select $select) use ($where) {
            $select->where($where);
        });
        if($row) {
            return $row->current();
        }
        return null;
    }
    /**
     * 批量更新侧边排序
     * @param array $data
     * @param array $where
     * @return int
     */
    public function allUpdateFrontside (array $data, array $where)
    {
        return $this->update($data, $where);
    }
    /**
     * 删除侧边信息
     * @param array $where
     * @return boolean|NULL
     */
    public function delFrontside (array $where)
    {
        $del = $this->delete($where);
        if($del) {
            return true;
        }
        return null;
    }
}