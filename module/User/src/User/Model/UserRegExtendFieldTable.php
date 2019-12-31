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

use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use User\Model\UserRegExtendField as dbshopCheckInData;

class UserRegExtendFieldTable  extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_user_reg_extend_field';

    public function setDbAdapter (Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加会员扩展字段信息
     * @param array $data
     * @return int|null
     */
    public function addUserRegExtendField(array $data)
    {
        $row = $this->insert(dbshopCheckInData::addUserRegExtendFieldData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 编辑会员扩展字段信息
     * @param array $data
     * @param array $where
     * @return int
     */
    public function editUserRegExtendField(array $data, array $where)
    {
        return $this->update($data, $where);
    }
    /**
     * 获取会员扩展字段信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoUserRegExtendField(array $where)
    {
        $row = $this->select($where);
        if($row) {
            return $row->current();
        }
        return null;
    }
    /**
     * 获取会员扩展信息列表（数组形式）
     * @param array|null $where
     * @return array|null
     */
    public function listUserRegExtendField(array $where=null)
    {
        $result = $this->select(function(Select $select) use ($where) {
            if(!empty($where)) $select->where($where);
            $select->order('field_sort ASC');
        });
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /**
     * 删除会员扩展信息
     * @param array $where
     * @return int
     */
    public function delUserRegExtendField(array $where)
    {
        return $this->delete($where);
    }
}