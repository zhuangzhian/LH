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

use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\AbstractTableGateway;
use \Zend\Db\Adapter\AdapterAwareInterface;
use Zend\Db\Adapter\Adapter;
use Theme\Model\ThemeGoods as dbshopCheckInData;

class ThemeGoodsTable extends AbstractTableGateway implements AdapterAwareInterface
{
    protected $table = 'dbshop_theme_goods';

    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加专题项目商品
     * @param array $data
     * @return int|null
     */
    public function addGoods(array $data)
    {
        $row = $this->insert(dbshopCheckInData::addThemeGoodsData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 更新专题项目商品
     * @param array $data
     * @param array $where
     * @return int
     */
    public function updateGoods(array $data, array $where)
    {
        return $this->update($data, $where);
    }
    /**
     * 获取项目商品信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoGoods(array $where)
    {
        $result = $this->select($where);
        if($result) {
            return $result->current();
        }
        return null;
    }
    /**
     * 商品列表
     * @param array $where
     * @return array|null
     */
    public function listGoods(array $where)
    {
        $result = $this->select(function (Select $select) use ($where) {
            $select->join(array('g'=>'dbshop_goods'), 'g.goods_id=dbshop_theme_goods.goods_id', array('goods_item', 'goods_state', 'goods_shop_price'));
            $select->join(array('ge'=>'dbshop_goods_extend'), 'ge.goods_id=dbshop_theme_goods.goods_id', array('goods_name'));
            $select->where($where);
            $select->order('dbshop_theme_goods.goods_sort ASC');
        });
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /**
     * 前台商品调用
     * @param array $where
     * @return array|null
     */
    public function frontShowGoods(array $where)
    {
        $result = $this->select(function (Select $select) use ($where) {
            $select->join(array('g'=>'dbshop_goods'), 'g.goods_id=dbshop_theme_goods.goods_id');
            $select->join(array('ge'=>'dbshop_goods_extend'), 'ge.goods_id=dbshop_theme_goods.goods_id', array('goods_name', 'goods_extend_name'));
            $select->columns(array('*', new Expression('
            (SELECT i.goods_thumbnail_image FROM dbshop_goods_image as i WHERE i.goods_image_id=ge.goods_image_id) as goods_thumbnail_image,
            (SELECT in_c.class_id FROM dbshop_goods_in_class as in_c WHERE in_c.goods_id=g.goods_id and in_c.class_state=1 LIMIT 1) as one_class_id,
            (SELECT SUM(og.buy_num) FROM dbshop_order_goods AS og INNER JOIN dbshop_order as do ON do.order_id=og.order_id WHERE og.goods_id=g.goods_id and do.order_state!=0) AS buy_num
            ')));
            $select->where($where);
            $select->where(array('g.goods_state'=>1));
            $select->order('dbshop_theme_goods.goods_sort ASC');
        });
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /**
     * 删除专题项目商品
     * @param array $where
     * @return int
     */
    public function delGoods(array $where)
    {
        return $this->delete($where);
    }
}