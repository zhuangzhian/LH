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

use Zend\Db\Sql\Ddl\AlterTable;
use Zend\Db\Sql\Ddl\Column\Char;
use Zend\Db\Sql\Ddl\Column\Integer;
use Zend\Db\Sql\Ddl\Column\Text;
use Zend\Db\Sql\Ddl\Column\Varchar;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;

class UserRegExtendTable  extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_user_reg_extend';

    public function setDbAdapter (Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加客户扩展信息内容
     * @param $data
     * @return bool|null
     */
    public function addUserRegExtend($data)
    {
        $row = $this->insert($data);
        if($row) {
            return true;
        }
        return null;
    }
    /**
     * 获取客户扩展信息内容
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoUserRegExtend(array $where)
    {
        $result = $this->select($where);
        if($result) {
            return $result->current();
        }
        return null;
    }
    /**
     * 编辑客户扩展信息内容
     * @param array $data
     * @param array $where
     * @return bool|null
     */
    public function editUserRegExtend(array $data, array $where)
    {
        $update = $this->update($data, $where);
        if($update) {
            return true;
        }
        return null;
    }
    /**
     * 扩展信息列表
     * @param array|null $where
     * @return array|null
     */
    public function listUserRegExtend(array $where=null)
    {
        $result = $this->select($where);
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /**
     * 删除客户扩展信息内容
     * @param array $where
     * @return int
     */
    public function delUserRegExtend(array $where)
    {
        return $this->delete($where);
    }
    /**
     * 创建字段
     * @param array $data
     */
    public function createColumn(array $data)
    {
        $table = new AlterTable($this->table);
        $columnData = $this->columnData($data);
        $table->addColumn($columnData);
        $sql = new Sql($this->adapter);
        $this->adapter->query($sql->getSqlStringForSqlObject($table))->execute();
    }
    /**
     * 删除字段
     * @param $columnName
     */
    public function dropColumn($columnName)
    {
        $table = new AlterTable($this->table);
        $table->dropColumn($columnName);
        $sql = new Sql($this->adapter);
        $this->adapter->query($sql->getSqlStringForSqlObject($table))->execute();
    }
    /**
     * 编辑字段
     * @param array $data
     */
    public function editColumn(array $data)
    {
        $table = new AlterTable($this->table);
        $columnData = $this->columnData($data);
        $table->changeColumn($data['field_name'], $columnData);
        $sql = new Sql($this->adapter);
        $this->adapter->query($sql->getSqlStringForSqlObject($table))->execute();
    }
    /**
     * 对需要创建的字段信息进行类型筛选
     * @param array $data
     * @return Char|Integer|Text|Varchar
     */
    private function columnData(array $data)
    {
        //$nullTable = $data['field_null'] == 1 ? true : false;
        $nullTable = true;
        switch($data['field_type']) {
            case 'radio':
            case 'select':
                $column = new Integer($data['field_name'], $nullTable);
                break;

            case 'checkbox':
            case 'upload':
                $column = new Varchar($data['field_name'], 500, $nullTable);
                break;

            case 'input':
                $column = new Char($data['field_name'], 255, $nullTable);
                break;

            case 'textarea':
                $column = new Text($data['field_name'], null, $nullTable);
                break;

            default:
                $column = new Char($data['field_name'], 255, $nullTable);
                break;
        }
        return $column;
    }
}