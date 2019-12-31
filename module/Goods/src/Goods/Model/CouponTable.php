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
use Goods\Model\Coupon as dbshopCheckInData;

class CouponTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_coupon';

    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 优惠券添加
     * @param array $data
     * @return int|null
     */
    public function addCoupon(array $data)
    {
        $row = $this->insert(dbshopCheckInData::addCouponData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 获取优惠规则信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoCoupon(array $where)
    {
        $result = $this->select($where);
        if($result) {
            return $result->current();
        }
        return null;
    }
    /**
     * 输出所有优惠券
     * @param array $where
     * @return array|null
     */
    public function listCoupon(array $where=array())
    {
        $result = $this->select(function(Select $select) use ($where) {
           $select->columns(array('*', new Expression('
           (SELECT count(ca.user_coupon_id) FROM dbshop_user_coupon AS ca WHERE ca.coupon_id=dbshop_coupon.coupon_id) AS ca_num,
           (SELECT count(cn.user_coupon_id) FROM dbshop_user_coupon AS cn WHERE cn.coupon_id=dbshop_coupon.coupon_id and cn.coupon_use_state IN (0,1)) AS cn_num,
           (SELECT count(cy.user_coupon_id) FROM dbshop_user_coupon AS cy WHERE cy.coupon_id=dbshop_coupon.coupon_id and cy.coupon_use_state=2) AS cy_num,
           (SELECT count(ce.user_coupon_id) FROM dbshop_user_coupon AS ce WHERE ce.coupon_id=dbshop_coupon.coupon_id and ce.coupon_use_state=3) AS ce_num
           ')));
            if(!empty($where)) $select->where($where);

            $select->order('coupon_id DESC');
        });
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /**
     * 用于获取优惠券的数组
     * @param array $where
     * @return array|null
     */
    public function couponArray(array $where=array(), $limitNum = 0)
    {
        $result = $this->select(function (Select $select) use ($where, $limitNum) {
           $select->where($where);
           if($limitNum > 0) $select->limit($limitNum);
        });
        //$result = $this->select($where);
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /**
     * 更新优惠券
     * @param array $data
     * @param array $where
     * @return int
     */
    public function updateCoupon(array $data, array $where)
    {
        return $this->update(dbshopCheckInData::updateCouponData($data), $where);
    }
    /**
     * 删除优惠券
     * @param array $where
     * @return int
     */
    public function delCoupon(array $where)
    {
        return $this->delete($where);
    }
}