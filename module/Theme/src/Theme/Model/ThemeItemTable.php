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
use Theme\Model\ThemeItem as dbshopCheckInData;

class ThemeItemTable extends AbstractTableGateway implements AdapterAwareInterface
{
    protected $table = 'dbshop_theme_item';

    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加专题项目
     * @param array $data
     * @return int|null
     */
    public function addItem(array $data)
    {
        $row = $this->insert(dbshopCheckInData::addThemeItemData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 更新专题项目
     * @param array $data
     * @param array $where
     * @return int
     */
    public function updateItem(array $data, array $where)
    {
        return $this->update($data, $where);
    }
    /**
     * 获取单条专题项目信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoItem(array $where)
    {
        $result = $this->select($where);
        if($result) {
            return $result->current();
        }
        return null;
    }
    /**
     * 获取专题项目数组
     * @param array $where
     * @return array|null
     */
    public function listItem(array $where)
    {
        $result = $this->select($where);
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /**
     * 获取专题广告项目数组
     * @param array $where
     */
    public function listAdItem(array $where)
    {
        $result = $this->select(function (Select $select) use ($where) {
            $select->columns(array('*', new Expression('(SELECT a.theme_ad_id FROM dbshop_theme_ad AS a WHERE a.item_id=dbshop_theme_item.item_id) AS theme_ad_id')));
            $select->where($where);
        });
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /**
     * 获取专题商品项目数组
     * @param array $where
     */
    public function listGoodsItem(array $where)
    {
        $result = $this->select(function (Select $select) use ($where) {
            $select->columns(array('*', new Expression('(SELECT count(g.theme_goods_id) FROM dbshop_theme_goods AS g WHERE g.item_id=dbshop_theme_item.item_id) AS goods_num')));
            $select->where($where);
        });
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /**
     * 删除项目专题
     * @param array $where
     * @return int
     */
    public function delItem(array $where)
    {
        return $this->delete($where);
    }
}