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

namespace Mobile\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ClassController extends AbstractActionController
{
    private $dbTables = array();
    private $translator;

    public function indexAction()
    {
        $array = array();

        $this->layout()->dbTongJiPage = 'class_list';

        $this->layout()->title_name   = $this->getDbshopLang()->translate('所有分类');

        //商品分类
        $arrayFile = DBSHOP_PATH . '/data/moduledata/Shopfront/goodsClass.php';
        //对商品分类数组进行写入文件处理
        if(file_exists($arrayFile)) {
            $array['goods_class'] = include $arrayFile;
        } else {
            //商品分类
            $array['goods_class']  = $this->getDbshopTable('GoodsClassTable')->classOptions(0,$this->getDbshopTable('GoodsClassTable')->listGoodsClass());
            if(is_array($array['goods_class']) and !empty($array['goods_class'])) {
                foreach ($array['goods_class'] as $key => $val) {
                    if($val['class_top_id'] != 0 and substr_count($val['class_path'], ',') == 1 and $val['class_state'] == 1) {
                        $array['goods_class'][$val['class_top_id']]['sub_class'][] = $val;
                        unset($array['goods_class'][$key]);
                    } elseif ($val['class_top_id'] != 0) unset($array['goods_class'][$key]);
                }
            }
            $arrayWrite = new \Zend\Config\Writer\PhpArray();
            $arrayWrite->toFile(DBSHOP_PATH . '/data/moduledata/Shopfront/goodsClass.php', $array['goods_class']);
        }


        return $array;
    }
    public function listAction()
    {
        $array = array();

        $classId = (int) $this->params('class_id');
        $array['class_info'] = $this->getDbshopTable('GoodsClassTable')->infoGoodsClass(array('class_id'=>$classId, 'class_state'=>1));

        //判断是否为手机端访问
        if(!$this->getServiceLocator()->get('frontHelper')->isMobile()) return $this->redirect()->toRoute('frontgoodslist/default', array('class_id'=>$classId));

        $this->layout()->dbTongJiPage = 'goods_class';
        //商品分类信息输出到layout
        $this->layout()->title_name         = $array['class_info']->class_name;
        $this->layout()->extend_title_name  = $array['class_info']->class_title_extend;
        $this->layout()->extend_keywords    = $array['class_info']->class_keywords;
        $this->layout()->extend_description = $array['class_info']->class_description;

        //商品下级分类
        $array['sub_class'] = $this->getDbshopTable('GoodsClassTable')->listGoodsClass(array('dbshop_goods_class.class_top_id'=>$classId));

        $getArray       = $this->request->getQuery()->toArray();
        /*===========================个性属性检索=================================*/
        //这里是获取最新的tag_id检索值
        $sTagArray      = ((isset($getArray['tag_id']) and !empty($getArray['tag_id'])) ? explode('|', $getArray['tag_id']) : array());
        //这里是对打散的tag内容进行get获取并组合
        $tagCArray      = ((isset($getArray['tag_c']) and !empty($getArray['tag_c'])) ? unserialize(base64_decode($getArray['tag_c'])) : array());
        if(!empty($sTagArray[0])) {
            //对tag_id进行整型处理，防止出现其他操作
            $sTagArray[1]   = (int) $sTagArray[1];
            $sTagArray[0]   = (int) $sTagArray[0];

            $tagCArray[$sTagArray[1]] = $sTagArray[0];
        } elseif (empty($sTagArray[0]) and !empty($sTagArray[1])) {
            unset($tagCArray[$sTagArray[1]]);
        }
        $array['s_tag'] = $tagCArray;
        //将已经检索的tag内容打散传入页面中
        $array['tag_c'] = !empty($array['s_tag']) ? str_replace('=', '', base64_encode(serialize($array['s_tag']))) : '';

        //生成tag_id的搜索字符串
        $sTagStr = '';
        if(!empty($array['s_tag'])){
            foreach ($array['s_tag'] as $st_val) {
                $st_val = (int) $st_val;//对组合后的tag_id进行重新整型处理，防止出现其他操作
                $sTagStr .= 'dbshop_goods.goods_tag_str like \'%,' . $st_val .',%\' and ';
            }
            $sTagStr  = substr($sTagStr, 0, -5);
        }
        //获取商品列表 商品分页
        $searchArray  = array('class_id'=>$classId, 'goods_state'=>1);
        if($sTagStr != '') $searchArray['goods_tag_str'] = $sTagStr;

        /*===========================个性属性检索=================================*/
        /*===========================排序检索=================================*/
        $sortArray = array();
        if(isset($getArray['time_sort'])  and !empty($getArray['time_sort']))  $sortArray['goods_add_time']  = $getArray['time_sort'];
        if(isset($getArray['click_sort']) and !empty($getArray['click_sort'])) $sortArray['goods_click']     = $getArray['click_sort'];
        if(isset($getArray['price_sort']) and !empty($getArray['price_sort'])) $sortArray['goods_shop_price+1']= $getArray['price_sort'];
        $sortArray       = (is_array($sortArray) and !empty($sortArray)) ? $sortArray : ((isset($getArray['sort_c']) and !empty($getArray['sort_c'])) ? unserialize(base64_decode($getArray['sort_c'])) : array());
        $array['sort_c'] = '';
        $sortStr         = 'goods_in.class_goods_sort ASC';
        if(!empty($sortArray)) {
            $sortKey         = key($sortArray);
            $sortValue       = current($sortArray);
            if(in_array($sortKey, array('goods_add_time', 'goods_click', 'goods_shop_price+1')) and in_array($sortValue, array('ASC', 'DESC'))) {
                $array['sort_c'] = str_replace('=', '', base64_encode(serialize(array($sortKey => $sortValue))));
                $sortStr         = 'dbshop_goods.' . $sortKey . ' ' . $sortValue;

                //这里之所以这样处理，是因为商品价格处使用char类型，需要+1排序才正常，下面的？语句，是为了对应模板中的排序选中状态
                $array['sort_selected'] = ($sortKey=='goods_shop_price+1' ? 'goods_shop_price' : $sortKey).$sortValue;
            }
        }
        /*===========================排序检索=================================*/
        //获取商品列表 商品分页
        $searchArray  = array('class_id'=>$classId, 'goods_state'=>1);
        if($sTagStr != '') $searchArray['goods_tag_str'] = $sTagStr;
        $innerTable   = array('goods_in_class'=>true);
        $userGroupId = $this->getServiceLocator()->get('frontHelper')->getUserSession('group_id');
        if($userGroupId > 0) {
            $searchArray['group_id'] = $userGroupId;
        }

        $page 		  = $this->params('page',1);
        $array['goods_list'] = $this->getDbshopTable('GoodsTable')->goodsPageList(array('page'=>$page, 'page_num'=>16), $searchArray, $innerTable, $sortStr);

        //个性属性
        $array['class_tag_group'] = $this->getDbshopTable('GoodsClassShowTable')->arrayGoodsClassTagGroup(array('class_id'=>$classId));
        if(is_array($array['class_tag_group']) and !empty($array['class_tag_group'])) {
            $tagArray   = $this->getDbshopTable('GoodsTagTable')->listGoodsTag(array('dbshop_goods_tag.tag_group_id IN (' . implode(',', $array['class_tag_group']) . ')', 'e.language'=>$this->getDbshopLang()->getLocale()) , array('tag_group_sort ASC', 'dbshop_goods_tag.tag_sort ASC'));
            $array['goods_tag'] = array();
            $array['goods_tag_group'] = array();
            if(is_array($tagArray) and !empty($tagArray)) {
                foreach ($tagArray as $tag_value) {
                    $array['goods_tag'][$tag_value['tag_group_id']][] = array('tag_id'=>$tag_value['tag_id'],'tag_name'=>$tag_value['tag_name']);
                    $array['goods_tag_group'][$tag_value['tag_group_id']] = $tag_value['tag_group_name'];
                }
            }
        }

        if($page > 1) {
            $view  = new ViewModel();
            $view->setTerminal(true);
            $view->setTemplate('/mobile/class/moregoodslist.phtml');

            return $view->setVariables($array);
        } else {
            return $array;
        }
    }
    /**
     * 品牌列表
     * @return array
     */
    public function brandAction()
    {
        $this->layout()->title_name     = $this->getDbshopLang()->translate('商品品牌');
        $this->layout()->dbTongJiPage   = 'brand';
        $array = array();
        //品牌列表
        $array['brand_array'] = $this->getDbshopTable('GoodsBrandTable')->listGoodsBrand();

        return $array;
    }
    /**
     * 品牌商品列表
     * @return array
     */
    public function brandListAction()
    {
        $array = array();

        $brandId = (int) $this->request->getQuery('brand_id');
        if($brandId <= 0) return $this->redirect()->toRoute('mobile/default');

        $array['brand_info'] = $this->getDbshopTable('GoodsBrandTable')->infoBrand(array('e.brand_id'=>$brandId));

        $this->layout()->dbTongJiPage = 'brand_list';
        //商品品牌信息输出到layout
        $this->layout()->title_name         = $array['brand_info']->brand_name;
        $this->layout()->extend_title_name  = $array['brand_info']->brand_title_extend;
        $this->layout()->extend_keywords    = $array['brand_info']->brand_keywords;
        $this->layout()->extend_description = $array['brand_info']->brand_description;

        $searchWhere = array();
        $sortArray   = array();
        $sortStr     = '';
        $getArray    = $this->request->getQuery()->toArray();
        /*===========================排序检索=================================*/
        if(isset($getArray['time_sort'])  and !empty($getArray['time_sort']))  $sortArray['goods_add_time']  = $getArray['time_sort'];
        if(isset($getArray['click_sort']) and !empty($getArray['click_sort'])) $sortArray['goods_click']     = $getArray['click_sort'];
        if(isset($getArray['price_sort']) and !empty($getArray['price_sort'])) $sortArray['goods_shop_price+1']= $getArray['price_sort'];
        $sortArray       = (is_array($sortArray) and !empty($sortArray)) ? $sortArray : ((isset($getArray['sort_c']) and !empty($getArray['sort_c'])) ? unserialize(base64_decode($getArray['sort_c'])) : array());
        if(!empty($sortArray)) {
            $sortKey         = key($sortArray);
            $sortValue       = current($sortArray);
            if(in_array($sortKey, array('goods_add_time', 'goods_click', 'goods_shop_price+1')) and in_array($sortValue, array('ASC', 'DESC'))) {
                $array['sort_c'] = base64_encode(serialize(array($sortKey => $sortValue)));
                $sortStr         = 'dbshop_goods.' . $sortKey . ' ' . $sortValue;

                //这里之所以这样处理，是因为商品价格处使用char类型，需要+1排序才正常，下面的？语句，是为了对应模板中的排序选中状态
                $array['sort_selected'] = ($sortKey=='goods_shop_price+1' ? 'goods_shop_price' : $sortKey).$sortValue;
            }

        }
        /*===========================排序检索=================================*/

        $userGroupId = $this->getServiceLocator()->get('frontHelper')->getUserSession('group_id');
        if($userGroupId > 0) {
            $searchWhere['group_id'] = $userGroupId;
        }
        //获取搜索商品列表 商品分页
        $searchWhere['goods_state']  = 1;
        $searchWhere['brand_id']     = $brandId;
        $page = $this->params('page',1);
        $array['goods_list'] = $this->getDbshopTable('GoodsTable')->searchGoods(array('page'=>$page, 'page_num'=>16), $searchWhere, $sortStr);

        if($page > 1) {
            $view  = new ViewModel();
            $view->setTerminal(true);
            $view->setTemplate('/mobile/class/morebrandlist.phtml');

            return $view->setVariables($array);
        } else {
            return $array;
        }
    }
    /**
     * 搜索页面
     * @return array
     */
    public function searchAction()
    {
        $this->layout()->dbTongJiPage = 'search';

        return array();
    }
    /**
     * 商品搜索
     * @return \Zend\View\Model\ViewModel
     */
    public function goodsSearchAction ()
    {
        $array = array();

        //顶部title使用
        $this->layout()->title_name = $this->getDbshopLang()->translate('商品搜索');

        $searchArray = array();
        $searchWhere = array();
        $sortArray   = array();
        $sortStr     = '';
        if($this->request->isGet()) {
            $searchArray               = $this->request->getQuery()->toArray();
            $array['keywords']         = isset($searchArray['keywords']) ? htmlentities($searchArray['keywords'], ENT_QUOTES, "UTF-8") : '';
            $searchWhere['goods_name'] = $array['keywords'];

            /*===========================排序检索=================================*/
            if(isset($searchArray['time_sort'])  and !empty($searchArray['time_sort']))  $sortArray['goods_add_time']  = $searchArray['time_sort'];
            if(isset($searchArray['click_sort']) and !empty($searchArray['click_sort'])) $sortArray['goods_click']     = $searchArray['click_sort'];
            if(isset($searchArray['price_sort']) and !empty($searchArray['price_sort'])) $sortArray['goods_shop_price+1']= $searchArray['price_sort'];
            $sortArray       = (is_array($sortArray) and !empty($sortArray)) ? $sortArray : ((isset($searchArray['sort_c']) and !empty($searchArray['sort_c'])) ? unserialize(base64_decode($searchArray['sort_c'])) : array());
            $array['sort_c'] = '';
            if(!empty($sortArray)) {
                $sortKey         = key($sortArray);
                $sortValue       = current($sortArray);
                if(in_array($sortKey, array('goods_add_time', 'goods_click', 'goods_shop_price+1')) and in_array($sortValue, array('ASC', 'DESC'))) {
                    $array['sort_c'] = str_replace('=', '', base64_encode(serialize(array($sortKey => $sortValue))));
                    $sortStr         = 'dbshop_goods.' . $sortKey . ' ' . $sortValue;

                    //这里之所以这样处理，是因为商品价格处使用char类型，需要+1排序才正常，下面的？语句，是为了对应模板中的排序选中状态
                    $array['sort_selected'] = ($sortKey=='goods_shop_price+1' ? 'goods_shop_price' : $sortKey).$sortValue;
                }
            }
            /*===========================排序检索=================================*/
        }
        //获取商品索引的状态，是否开启
        $goodsIndexState = $this->getServiceLocator()->get('frontHelper')->getDbshopGoodsIni('goods_index', '');
        $array['goods_index_state'] = $goodsIndexState;

        $userGroupId = $this->getServiceLocator()->get('frontHelper')->getUserSession('group_id');
        if($userGroupId > 0) {
            $searchWhere['group_id'] = $userGroupId;
        }
        //获取搜索商品列表 商品分页
        $searchWhere['goods_state']  = 1;
        $page = $this->params('page',1);
        if($goodsIndexState == 'true')
            $array['goods_list'] = $this->getDbshopTable('GoodsIndexTable')->searchGoods(array('page'=>$page, 'page_num'=>16), $searchWhere, $sortStr);
        else
            $array['goods_list'] = $this->getDbshopTable('GoodsTable')->searchGoods(array('page'=>$page, 'page_num'=>16), $searchWhere, $sortStr);


        //统计使用
        $this->layout()->dbTongJiPage      = 'goods_search';
        $this->layout()->tj_search_keywords= $array['keywords'];
        $this->layout()->tj_search_count   = $array['goods_list']->getTotalItemCount();

        if($page > 1) {
            $view  = new ViewModel();
            $view->setTerminal(true);
            $view->setTemplate('/mobile/class/moregoodssearch.phtml');

            return $view->setVariables($array);
        } else {
            return $array;
        }
    }
    /**
     * 数据表调用
     * @param string $tableName
     * @return multitype:
     */
    private function getDbshopTable ($tableName)
    {
        if (empty($this->dbTables[$tableName])) {
            $this->dbTables[$tableName] = $this->getServiceLocator()->get($tableName);
        }
        return $this->dbTables[$tableName];
    }
    /**
     * 语言包调用
     * @return Ambigous <object, multitype:, \Zend\I18n\Translator\Translator>
     */
    private function getDbshopLang ()
    {
        if (! $this->translator) {
            $this->translator = $this->getServiceLocator()->get('translator');
        }
        return $this->translator;
    }
} 