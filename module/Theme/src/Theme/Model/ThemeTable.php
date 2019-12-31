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
use Theme\Model\Theme as dbshopCheckInData;

class ThemeTable extends AbstractTableGateway implements AdapterAwareInterface
{
    protected $table = 'dbshop_theme';

    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加专题信息
     * @param array $data
     * @return int|null
     */
    public function addTheme(array $data)
    {
        $row = $this->insert(dbshopCheckInData::addThemeData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 更新专题信息
     * @param array $data
     * @param array $where
     * @return int
     */
    public function updateTheme(array $data, array $where)
    {
        return $this->update($data, $where);
    }
    /**
     * 获取单条专题信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoTheme(array $where)
    {
        $result = $this->select($where);
        if($result) {
            return $result->current();
        }
        return null;
    }
    /**
     * 获取专题信息数组（列表）
     * @param array $where
     * @return array|null
     */
    public function listTheme(array $where=array())
    {
        $result = $this->select($where);
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /**
     * 删除专题信息
     * @param array $where
     * @return int
     */
    public function delTheme(array $where)
    {
        return $this->delete($where);
    }
}