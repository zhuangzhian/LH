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

namespace User\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use \Zend\Db\Adapter\AdapterAwareInterface;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;
use User\Model\Cart as dbshopCheckInData;

class CartTable extends AbstractTableGateway implements AdapterAwareInterface
{
    protected $table = 'dbshop_cart';

    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加购物车
     * @param array $data
     * @return int|null
     */
    public function addCart (array $data)
    {
        $row = $this->insert(dbshopCheckInData::addCartData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 获取购物车信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoCart(array $where)
    {
        $result = $this->select($where);
        if($result) {
            return $result->current();
        }
        return null;
    }
    /**
     * 更新购物车
     * @param array $data
     * @param array $where
     */
    public function editCart(array $data, array $where)
    {
        $this->update($data, $where);
    }
    /**
     * 删除购物车内的商品
     * @param array $where
     * @return int
     */
    public function delCart(array $where)
    {
        return $this->delete($where);
    }
    /**
     * 购物车中的商品
     * @param array $where
     * @return array|null
     */
    public function cartGoods(array $where)
    {
        $result = $this->select($where);
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /**
     * 获取商品中的购物车种类数量
     * @param array $where
     * @return int
     */
    public function cartGoodsCount(array $where)
    {
        $result = $this->select($where);
        return $result->count();
    }
}