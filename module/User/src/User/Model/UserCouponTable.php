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

namespace User\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use User\Model\UserCoupon as dbshopCheckInData;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;
/**
 * 会员优惠券表
 * Class UserCouponTable
 * @package User\Model
 */
class UserCouponTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_user_coupon';

    public function setDbAdapter (Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加会员优惠券
     * @param array $data
     * @return int|null
     */
    public function addUserCoupon(array $data)
    {
        $row = $this->insert(dbshopCheckInData::addUserCouponData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 更新会有优惠券信息
     * @param array $data
     * @param array $where
     * @return int
     */
    public function updateUserCoupon(array $data, array $where)
    {
        return $this->update($data, $where);
    }
    /**
     * 获取优惠券信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoUserCoupon(array $where)
    {
        $result = $this->select($where);
        if($result) {
            return $result->current();
        }
        return null;
    }
    /**
     * 会员优惠券列表（分页）
     * @param array $pageArray
     * @param array $where
     * @return Paginator
     */
    public function listPageUserCoupon(array $pageArray, array $where=array())
    {
        $select = new Select(array('user_coupon'=>$this->table));
        //$where  = dbshopCheckInData::whereUserData($where);

        $select->join(array('co'=>'dbshop_coupon'), 'co.coupon_id=user_coupon.coupon_id',array('coupon_goods_type', 'coupon_goods_body', 'shopping_discount', 'coupon_use_channel'));
        $select->where($where);
        $select->order('user_coupon.user_coupon_id DESC');

        //实例化分页处理
        $pageAdapter = new DbSelect($select, $this->adapter);
        $paginator   = new Paginator($pageAdapter);
        $paginator->setCurrentPageNumber($pageArray['page']);
        $paginator->setItemCountPerPage($pageArray['page_num']);

        return $paginator;
    }
    /**
     * 会员优惠券列表
     * @param array $where
     * @return array|null
     */
    public function listUserCoupon(array $where)
    {
        $result = $this->select($where);
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /**
     * 会员优惠券列表（前台优惠券使用时使用）
     * @param array $where
     * @return array|null
     */
    public function useUserCoupon(array $where)
    {
        $result = $this->select(function(Select $select) use ($where){
            $select->join(array('co'=>'dbshop_coupon'), 'co.coupon_id=dbshop_user_coupon.coupon_id', array('shopping_discount'));
            $select->where($where);
        });
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /**
     * 会员优惠券删除功能
     * @param array $where
     * @return int
     */
    public function delUserCoupon(array $where)
    {
        return $this->delete($where);
    }
    /**
     * 前台会员优惠券的统计信息
     * @param $userId
     * @return array
     */
    public function allStateNumCoupon($userId)
    {
        $array = array();
        $array['all'] = $this->select(array('user_id'=>$userId))->count();
        $array['0'] = $this->select(array('coupon_use_state'=>0, 'user_id'=>$userId))->count();
        $array['1'] = $this->select(array('coupon_use_state'=>1, 'user_id'=>$userId))->count();
        $array['2'] = $this->select(array('coupon_use_state'=>2, 'user_id'=>$userId))->count();
        $array['3'] = $this->select(array('coupon_use_state'=>3, 'user_id'=>$userId))->count();

        return $array;
    }
}