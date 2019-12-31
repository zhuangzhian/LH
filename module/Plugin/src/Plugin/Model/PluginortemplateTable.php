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

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Plugin\Model\Pluginortemplate as dbshopCheckInData;

class PluginortemplateTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_pluginortemplate_update';

    public function setDbAdapter (Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加信息
     * @param array $data
     * @return int|null
     */
    public function addPluginorTemplate(array $data)
    {
        $row = $this->insert(dbshopCheckInData::addPluginortemplateData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 更新信息
     * @param array $data
     * @param array $where
     * @return int
     */
    public function updatePluginorTemplate(array $data, array $where)
    {
        return $this->update($data, $where);
    }
    /**
     * 获取信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoPluginorTemplate(array $where)
    {
        $result = $this->select($where);
        if($result) {
            return $result->current();
        }
        return null;
    }
    /**
     * 删除信息
     * @param array $where
     * @return int
     */
    public function delPluginorTemplate(array $where)
    {
        return $this->delete($where);
    }
}