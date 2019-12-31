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

namespace Goods\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Goods\Model\VirtualGoods as dbshopCheckInData;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class VirtualGoodsTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_virtual_goods';
    
    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 虚拟商品添加
     * @param array $data
     * @return int|null
     */
    public function addVirtualGoods(array $data)
    {
        $row = $this->insert(dbshopCheckInData::addVirtualGoodsData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 虚拟商品列表
     * @param array $where
     * @return array|null
     */
    public function listVirtualGoods(array $where=array())
    {
        $result = $this->select(function  (Select $select) use ($where)
        {
            $select->where($where)->order(array('dbshop_virtual_goods.virtual_goods_id DESC'));
        });
        if($result) {
            return $result->toArray();
        }
        
        return null;
    }
    /**
     * 分页
     * @param array $pageArray
     * @param array $where
     * @return Paginator
     */
    public function pageVirtualGoods(array $pageArray, array $where=array())
    {
        $select = new Select(array('dbshop_virtual_goods'=>$this->table));
        $select->where($where)->order(array('dbshop_virtual_goods.virtual_goods_id DESC'));

        //实例化分页处理
        $pageAdapter = new DbSelect($select, $this->adapter);
        $paginator   = new Paginator($pageAdapter);
        $paginator->setCurrentPageNumber($pageArray['page']);
        $paginator->setItemCountPerPage($pageArray['page_num']);

        return $paginator;
    }
    /**
     * 虚拟商品信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoVirtualGoods(array $where)
    {
    	$result = $this->select($where);
    	if($result) {
    		return $result->toArray();
    	}
    	return null;
    }
    /**
     * 删除虚拟商品信息
     * @param array $where
     * @return bool|null
     */
    public function delVirtualGoods(array $where)
    {
    	$del = $this->delete($where);
    	if($del) {
    		return true;
    	}
    	return null;
    }
    /**
     * 更新虚拟商品信息
     * @param array $data
     * @param array $where
     * @return bool|null
     */
    public function updateVirtualGoods(array $data, array $where)
    {
    	$update = $this->update(dbshopCheckInData::updateVirtualGoodsData($data), $where);
    	if($update) {
    		return true;
    	}
    	return null;
    }
    /**
     * 根据条件获取虚拟商品总数
     * @param array $where
     * @return int
     */
    public function countVirtualGoods(array $where)
    {
        return $this->select($where)->count();
    }
}

?>