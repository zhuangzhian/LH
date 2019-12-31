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

use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Goods\Model\GoodsAdvSpecGroup as dbshopCheckInData;

class GoodsAdvSpecGroupTable  extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_goods_adv_spec_group';

    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加规格信息
     * @param array $data
     * @return int
     */
    public function addGoodsAdvSpecGroup(array $data)
    {
        return $this->insert(dbshopCheckInData::addGoodsAdvSpecGroupData($data));
    }
    /**
     * 更新规格信息
     * @param array $data
     * @param array $where
     * @return int
     */
    public function updateGoodsAdvSpecGroup(array $data, array $where)
    {
        return $this->update(dbshopCheckInData::updateGoodsAdvSpecGroupData($data), $where);
    }
    /**
     * 获取规格信息
     * @param array $where
     * @return array|null
     */
    public function listGoodsAdvSpecGroup(array $where)
    {
        $result = $this->select($where);
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /**
     * 获取规格信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoGoodsAdvSpecGroup(array $where)
    {
        $result = $this->select($where);
        if($result) {
            return $result->current();
        }
        return null;
    }
    /**
     * 删除规格信息
     * @param array $where
     * @return int
     */
    public function delGoodsAdvSpecGroup(array $where)
    {
        return $this->delete($where);
    }
}
