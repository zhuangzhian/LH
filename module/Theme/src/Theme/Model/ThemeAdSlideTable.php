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

use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\AbstractTableGateway;
use \Zend\Db\Adapter\AdapterAwareInterface;
use Zend\Db\Adapter\Adapter;
use Theme\Model\ThemeAdSlide as dbshopCheckInData;

class ThemeAdSlideTable extends AbstractTableGateway implements AdapterAwareInterface
{
    protected $table = 'dbshop_theme_ad_slide';

    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加幻灯片
     * @param array $data
     * @return int|null
     */
    public function addAdSlide(array $data)
    {
        $row = $this->insert(dbshopCheckInData::addThemeAdSlideData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 更新幻灯片信息
     * @param array $data
     * @param array $where
     * @return int
     */
    public function updateAdSlide(array $data, array $where)
    {
        return $this->update($data, $where);
    }
    /**
     * 获取幻灯片信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoAdSlide(array $where)
    {
        $result = $this->select($where);
        if($result) {
            return $result->current();
        }
        return null;
    }
    /**
     * 获取幻灯片数组信息
     * @param array $where
     * @return array|null
     */
    public function listAdSlide(array $where)
    {
        $result = $this->select(function (Select $select) use ($where){
            $select->where($where)->order('theme_ad_slide_sort ASC');
        });
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /**
     * 删除幻灯片
     * @param array $where
     * @return null
     */
    public function delAdSlide(array $where)
    {
        $slideArray = $this->listAdSlide($where);
        if(!empty($slideArray)) {
            foreach ($slideArray as $value) {
                if(!empty($value['theme_ad_slide_image'])) unlink(DBSHOP_PATH . $value['theme_ad_slide_image']);
                $this->delete(array('theme_ad_id'=>$value['theme_ad_id']));
            }
            return true;
        }
        return null;
    }
    /**
     * 删除幻灯片单一内容
     * @param array $where
     * @return int
     */
    public function delSlideData(array $where)
    {
        return $this->delete($where);
    }
}