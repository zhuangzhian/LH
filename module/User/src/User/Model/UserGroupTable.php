<?php
/**
 * DBShop 电子商务系统
 *
 * ==========================================================================
 * @link      http://www.dbshop.net/
 * @copyright Copyright (c) 2012-2015 DBShop.net Inc. (http://www.dbshop.net)
 * @license   http://www.dbshop.net/license.html License
 * ==========================================================================
 *
 * @author    静静的风
 *
 */

namespace User\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use User\Model\UserGroup as dbshopCheckInData;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;

class UserGroupTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_user_group';
    
    public function setDbAdapter (Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加客户组
     * @param array $data
     * @return int|null
     */
    public function addUserGroup (array $data)
    {
        $row = $this->insert(dbshopCheckInData::addUserGroupData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 获取客户组信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoUserGroup (array $where)
    {
        $result = $this->select($where);
        if($result) {
            return $result->current();
        }
        return null;
    }
    /**
     * 客户组列表
     * @param array $where
     * @return array|null
     */
    public function listUserGroup (array $where=array(), array $order = array())
    {
        $result = $this->select(function (Select $select) use ($where, $order) {
            $select->join(array('e'=>'dbshop_user_group_extend'), 'e.group_id=dbshop_user_group.group_id',array('*'))
            ->where($where);
            if(!empty($order)) $select->order($order);
        });
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /**
     * 更新客户组信息
     * @param array $data
     * @param array $where
     * @return bool
     */
    public function updateUserGroup (array $data, array $where)
    {
        $update = $this->update(dbshopCheckInData::updateUserGroupData($data), $where);
        if($update) {
            return true;
        }
        return false;
    }
    /**
     * 删除客户组信息
     * @param array $where
     * @return bool|null
     */
    public function delUserGroup (array $where)
    {
        $del = $this->delete($where);
        
        if($del) {
            $sql = new Sql($this->adapter);
            $sql->prepareStatementForSqlObject($sql->delete('dbshop_user_group_extend')->where($where))->execute();
            return true;
        }
        return null;
    }
    /**
     * 检测客户组是否需要更换
     * @param array $data
     * @return null
     */
    public function checkUserGroup (array $data)
    {
        //如果会员组没有开启等级积分，那么直接返回
        if(isset($data['group_id'])) {
            $result = $this->select(array('group_id' => $data['group_id'], 'integral_num_state' => 0));
            if($result->current()) {
                return $data['group_id'];
            }
        }

        //如果是开启了积分判断等级，那么进行下面的处理
        $result = $this->select(function(Select $select) {
           $select->order('integral_num_start ASC');
        });
        if($result) {
            $groupArray = $result->toArray();
            if(is_array($groupArray) and !empty($groupArray)) {
                foreach($groupArray as $value) {
                    if($value['integral_num_state'] == 1) {
                        if($value['integral_num_start'] <= $data['integral_num'] and $value['integral_num_end'] >= $data['integral_num']) {
                            return ($value['group_id'] == $data['group_id'] ? null : $value['group_id']);
                        }
                    }
                }
            }
        }
        return null;
    }
}

?>