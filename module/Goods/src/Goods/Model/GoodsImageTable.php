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

namespace Goods\Model;

use OSS\Core\OssException;
use OSS\OssClient;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Goods\Model\GoodsImage as dbshopCheckInData;
use Zend\Db\Sql\Select;

class GoodsImageTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{

    protected $table = 'dbshop_goods_image';
    private $configReader;
    
    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加图片
     * @param array $array
     * @return int|null
     */
    public function addImage (array $array)
    {
        $row = $this->insert(dbshopCheckInData::addGoodsImageData($array));
        if ($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 商品图片显示
     * @param array $array
     * @return \Zend\Db\ResultSet\ResultSet
     */
    public function listImage (array $array = array())
    {
        $result = $this->select(function  (Select $select) use($array)
        {
            if (is_array($array) and ! empty($array)) {
                foreach ($array as $val) {
                    $select->where($val);
                }
            }
            $select->order('image_sort ASC');
            $select->order('goods_image_id ASC');
        });
        return $result;
    }
    /**
     * 商品图片更新
     * @param array $data
     * @param array $where
     * @return int
     */
    public function updateImage (array $data, array $where = array())
    {
        return $this->update(dbshopCheckInData::updateGoodsImageData($data), $where);
    }
    /**
     * 批量修改商品图片排序
     * @param array $imagesSort
     * @param array $imagesId
     */
    public function updateImagesSort (array $imagesSort, array $imagesId)
    {
        foreach($imagesId as $imageId) {
            $this->update(array('image_sort'=>$imagesSort['image_sort_'.$imageId]), array('goods_image_id'=>$imageId));
        }
    }
    /**
     * 修改单独商品图片
     * @param array $imageArray
     * @param array $where
     */
    public function updateOneImage(array $imageArray, array $where)
    {
        $this->update($imageArray, $where);
    }
    /**
     * 获取单个图片信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoImage (array $where)
    {
        $row = $this->select($where);
        if($row) {
            return $row->current();
        }
        return null;
    }
    /**
     * 删除图片
     * @param $image_id
     * @return bool|null
     */
    public function delImage ($image_id)
    {
        if (is_numeric($image_id) and intval($image_id) == 0)
            return null;
        if (! is_array($image_id))
            $image_id = array(
                $image_id
            );
        if (is_array($image_id) and ! empty($image_id)) {
            foreach ($image_id as $value) {
                $row = $this->select(array(
                    'goods_image_id' => $value
                ))->current();
                if ($row) {
                    $this->delYunGoodsImage(array(
                        'goods_title_image'     => $row->goods_title_image,
                        'goods_thumbnail_image' => $row->goods_thumbnail_image,
                        'goods_watermark_image' => $row->goods_watermark_image,
                        'goods_source_image'    => $row->goods_source_image,
                    ));
                    /*@unlink(DBSHOP_PATH . $row->goods_title_image);
                    @unlink(DBSHOP_PATH . $row->goods_thumbnail_image);
                    @unlink(DBSHOP_PATH . $row->goods_watermark_image);
                    @unlink(DBSHOP_PATH . $row->goods_source_image);*/

                    $this->delete(array(
                        'goods_image_id' => $value
                    ));
                }
            }
            return true;
        } else {
            return null;
        }
    }
    /**
     * 删除单个或多个商品内的图片
     * @param array $where
     * @return int|null
     */
    public function delGoodsImage (array $where)
    {
        $whereStr = 'goods_id IN (' . implode(',', $where) . ')';
        $result   = $this->select($whereStr)->toArray();
        if(is_array($result) and !empty($result)) {
            foreach ($result as $value) {
                $this->delYunGoodsImage($value);
                /*@unlink(DBSHOP_PATH . $value['goods_title_image']);
                @unlink(DBSHOP_PATH . $value['goods_thumbnail_image']);
                @unlink(DBSHOP_PATH . $value['goods_watermark_image']);
                @unlink(DBSHOP_PATH . $value['goods_source_image']);*/
            }
            return $this->delete($whereStr);
        }
        return null;
    }
    private function delYunGoodsImage($imageArray)
    {
        if(empty($this->configReader)) {
           $this->configReader = new \Zend\Config\Reader\Ini();
        }
        $storageConfig = $this->configReader->fromFile(DBSHOP_PATH . '/data/moduledata/Upload/Storage.ini');

        if(strpos($imageArray['goods_title_image'], '{qiniu}') !== false) $this->delQiniuGoodsImage($imageArray, $storageConfig);
        if(strpos($imageArray['goods_title_image'], '{aliyun}') !== false) $this->delAliyunGoodsImage($imageArray, $storageConfig);

        if(strpos($imageArray['goods_title_image'], '{qiniu}') === false and strpos($imageArray['goods_title_image'], '{aliyun}') === false) {
            @unlink(DBSHOP_PATH . $imageArray['goods_title_image']);
            @unlink(DBSHOP_PATH . $imageArray['goods_thumbnail_image']);
            @unlink(DBSHOP_PATH . $imageArray['goods_watermark_image']);
            @unlink(DBSHOP_PATH . $imageArray['goods_source_image']);
        }
    }
    /**
     * 七牛图片删除
     * @param $imageArray
     * @param $storageConfig
     */
    private function delQiniuGoodsImage($imageArray, $storageConfig)
    {
        $auth       = new \Qiniu\Auth($storageConfig['qiniu_ak'], $storageConfig['qiniu_sk']);
        $bucketMgr  = new \Qiniu\Storage\BucketManager($auth);
        $bucket     = $storageConfig['qiniu_space_name'];
        $bucketMgr->delete($bucket, basename($imageArray['goods_title_image']));
        $bucketMgr->delete($bucket, basename($imageArray['goods_thumbnail_image']));
    }
    /**
     * 阿里云图片删除
     * @param $imageArray
     * @param $storageConfig
     */
    private function delAliyunGoodsImage($imageArray, $storageConfig)
    {
        $aliyunOssDomainType    =  $storageConfig['aliyun_domain_type'] == 'true' ? true : false;
        $aliyunOssDomain        = $storageConfig['aliyun_http_type'] . ($aliyunOssDomainType ? $storageConfig['aliyun_domain'] : (isset($storageConfig['aliyun_oss_domain']) ? $storageConfig['aliyun_oss_domain'] : str_replace($storageConfig['aliyun_space_name'].'.', '', $storageConfig['aliyun_domain'])));
        try{
            $OssClient = new OssClient($storageConfig['aliyun_ak'], $storageConfig['aliyun_sk'], $aliyunOssDomain, $aliyunOssDomainType);
            $OssClient->deleteObjects($storageConfig['aliyun_space_name'], array(basename($imageArray['goods_title_image']), basename($imageArray['goods_thumbnail_image'])));
        }catch (OssException $e) {

        }
    }
}

?>