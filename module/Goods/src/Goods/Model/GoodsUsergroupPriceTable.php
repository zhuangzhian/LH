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
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Goods\Model\GoodsUsergroupPrice as dbshopCheckInData;

class GoodsUsergroupPriceTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_goods_usergroup_price';

    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加客户组价格
     * @param array $data
     * @return bool|null
     */
    public function addGoodsUsergroupPrice(array $data)
    {
        $row = $this->insert(dbshopCheckInData::addGoodsUserGroupPriceData($data));
        if($row) {
            return true;
        }
        return null;
    }
    /**
     * 编辑客户组价格
     * @param array $data
     * @param array $where
     * @return bool|null
     */
    public function editGoodsUsergroupPrice(array $data, array $where)
    {
        $update = $this->update(dbshopCheckInData::updateGoodsGroupPriceData($data), $where);
        if($update) {
            return true;
        }
        return null;
    }
    /**
     * 获取客户组价格信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoGoodsUsergroupPrice(array $where)
    {
        $result = $this->select($where);
        if($result) {
            return $result->current();
        }
        return null;
    }
    /**
     * 客户价格数组信息
     * @param array $where
     * @return array|null
     */
    public function listGoodsUsergroupPrice(array $where)
    {
        $result = $this->select(function (Select $select) use ($where) {
            $select->columns(array('*',
                new Expression('
                    (SELECT u.group_name FROM dbshop_user_group_extend as u WHERE u.group_id=dbshop_goods_usergroup_price.user_group_id) as group_name
                    ')
            ));
            $select->where($where);
        });
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /**
     * 删除客户组价格
     * @param array $where
     * @return bool|null
     */
    public function delGoodsUsergroupPrice(array $where)
    {
        $del = $this->delete($where);
        if($del) {
            return true;
        }
        return null;
    }
}