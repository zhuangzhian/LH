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

namespace Shopfront\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container;
use Zend\View\Model\ViewModel;

class GoodsController extends AbstractActionController
{
    private $dbTables = array();
    private $translator;

    /**
     * 商品详细内容页
     */
    public function indexAction ()
    {
        $array = array();

        $goodsId = (int) $this->params('goods_id');
        $classId = (int) $this->params('class_id');

        if($goodsId <= 0 or $classId <= 0) return $this->redirect()->toRoute('shopfront/default');

        //判断是否为手机端访问
        if($this->getServiceLocator()->get('frontHelper')->isMobile()) return $this->redirect()->toRoute('m_goods/default', array('goods_id'=>$goodsId, 'class_id'=>$classId));

        //分类信息及分类面包屑导航
        $classInfo = $this->getDbshopTable('GoodsClassTable')->infoGoodsClass(array('class_id'=>$classId));
        if(!$classInfo) return $this->redirect()->toRoute('shopfront/default');
        $array['class_menu'] = $this->getDbshopTable('GoodsClassTable')->selectGoodsClass('class_id IN ('.$classInfo->class_path.')', array('class_path ASC'));
        $array['class_id']   = $classId;
        
        //商品基本信息
        $array['goods_info']   = $this->getDbshopTable('GoodsTable')->infoGoods(array('dbshop_goods.goods_id'=>$goodsId, 'e.language'=>$this->getDbshopLang()->getLocale()));

        if(!$array['goods_info']) return $this->redirect()->toRoute('shopfront/default');
        
        //判断优惠价格是否存在，是否过期
        $preferentialStart = (intval($array['goods_info']->goods_preferential_start_time) == 0 or time() >= $array['goods_info']->goods_preferential_start_time) ? true : false;
        $preferentialEnd   = (intval($array['goods_info']->goods_preferential_end_time) == 0 or time() <= $array['goods_info']->goods_preferential_end_time) ? true : false;
        $array['goods_info']->goods_preferential_price = ($preferentialStart and $preferentialEnd and $array['goods_info']->goods_preferential_price > 0) ? $array['goods_info']->goods_preferential_price : 0;

        //判断是否登录，是否有会员组价格
        $userGroupId = $this->getServiceLocator()->get('frontHelper')->getUserSession('group_id');
        if($array['goods_info']->goods_preferential_price <= 0 and $userGroupId > 0) {
            $userGroupPrice = $this->getDbshopTable('GoodsUsergroupPriceTable')->infoGoodsUsergroupPrice(array('goods_id'=>$goodsId, 'user_group_id'=>$userGroupId, 'goods_color'=>'', 'goods_size'=>'', 'adv_spec_tag_id'=>''));
            if(isset($userGroupPrice->goods_user_group_price) and $userGroupPrice->goods_user_group_price > 0) $array['goods_info']->goods_shop_price = $userGroupPrice->goods_user_group_price;
        }

        //顶部title使用
        $this->layout()->title_name         = $array['goods_info']->goods_name;
        $this->layout()->extend_title_name  = $array['goods_info']->goods_extend_name;
        $this->layout()->extend_keywords    = $array['goods_info']->goods_keywords;
        $this->layout()->extend_description = $array['goods_info']->goods_description;

        //商品库存显示
        $array['goods_stock'] = $array['goods_info']->goods_stock;//默认库存数
        if(!defined('FRONT_CACHE_STATE') or FRONT_CACHE_STATE != 'true') {//在没有开启缓存的情况下启用
            $stock_state_id       = '';
            if ($array['goods_info']->goods_stock <= $array['goods_info']->goods_out_of_stock_set) {//当库存达到缺货时
                $stock_state_id = $array['goods_info']->goods_out_stock_state;
            } elseif($array['goods_info']->goods_stock_state_open == 1) {//当启用库存状态显示，且库存充足
                $stock_state_id = $array['goods_info']->goods_stock_state;
            }
            if($stock_state_id != '') {//说明有文字库存显示，替换默认库存
                $stockStateInfo       = $this->getDbshopTable('StockStateTable')->infoStockState(array('e.stock_state_id'=>$stock_state_id, 'e.language'=>$this->getDbshopLang()->getLocale()));
                $array['goods_stock'] = $stockStateInfo->stock_state_name;
            }
        }

        //商品图片
        $array['goods_images'] = $this->getDbshopTable('GoodsImageTable')->listImage(array('goods_id='.$goodsId, 'image_slide=1'))->toArray();
        
        //商品自定义信息
        $array['goods_custom'] = $this->getDbshopTable('GoodsCustomTable')->listGoodsCustom(array('goods_id'=>$goodsId, 'custom_content_state'=>1));
        
        //商品品牌
        if($array['goods_info']->brand_id != '') $array['brand_info'] = $this->getDbshopTable('GoodsBrandTable')->infoBrand(array('e.brand_id'=>$array['goods_info']->brand_id, 'e.language'=>$this->getDbshopLang()->getLocale()));
		
        //商品评分
        $array['goods_rating'] = $this->getDbshopTable('GoodsCommentTable')->calculateGoodsRating(array('dbshop_goods_comment.goods_id'=>$goodsId));
        
        //商品属性
        if($array['goods_info']->attribute_group_id != '') {
            $array['goods_attribute'] = $this->getAttributeArray($array['goods_info']->attribute_group_id, $goodsId);
        }
        
        //相关商品
        $array['related_goods_array'] = $this->getDbshopTable('GoodsRelatedTable')->listRelatedGoods(array('dbshop_goods_related.goods_id'=>$goodsId, 'g.goods_state'=>1, 'e.language'=>$this->getDbshopLang()->getLocale()));        
        if(empty($array['related_goods_array'])) {//如果没有设置相关商品，随机显示8个同类的相关商品
            $array['related_goods_array'] = $this->getDbshopTable('GoodsTable')->allGoods(array('rand_related_goods'=>'dbshop_goods.goods_id!='.$goodsId.' and goods_in.class_id='.$classId.' and dbshop_goods.goods_state=1'), array('goods_in_class'=>true), array('rand()'), 8);
            if(!empty($array['related_goods_array'])) {
                foreach($array['related_goods_array'] as $related_key => $related_value) {
                    $related_value['related_goods_id'] = $related_value['goods_id'];
                    $array['related_goods_array'][$related_key] = $related_value;
                }
            }
        }
        //关联商品
        $array['relation_goods_array'] = $this->getDbshopTable('GoodsRelationTable')->listRelationGoods(array('dbshop_goods_relation.goods_id'=>$goodsId, 'g.goods_state'=>1, 'e.language'=>$this->getDbshopLang()->getLocale()), array("dbshop_goods_relation.relation_sort ASC"));
        //组合商品
        $array['combination_goods_array'] = $this->getDbshopTable('GoodsCombinationTable')->listCombinationGoods(array('dbshop_goods_combination.goods_id'=>$goodsId, 'g.goods_state'=>1, 'e.language'=>$this->getDbshopLang()->getLocale()));
        //事件驱动，显示商品信息时的一些抛出
        $response = $this->getEventManager()->trigger('goods.info.front.post', $this, array('values'=>$array));
        if(!$response->isEmpty()) {
            $num = $response->count();
            for($i = 0; $i < $num; $i++) {
                $preArray = $response->offsetGet($i);
                if(!empty($preArray)) $array[key($preArray)] = current($preArray);
                unset($preArray);
            }
        }
        //客服代码
        $this->layout()->kefu_html = $this->getServiceLocator()->get('frontHelper')->getOnlineService('goods');
        
        //统计使用
        $this->layout()->dbTongJiPage            = 'goods_body';
        $this->layout()->tongji_goods_class_id   = $array['class_id'];
        $this->layout()->tongji_goods_brand_id   = $array['goods_info']->brand_id;
        $this->layout()->tongji_goods_name       = $array['goods_info']->goods_name;
        $this->layout()->tongji_goods_id         = $array['goods_info']->goods_id;
        $this->layout()->tongji_goods_image      = $this->getServiceLocator()->get('frontHelper')->dbshopHttpOrHttps() . $this->getServiceLocator()->get('frontHelper')->dbshopHttpHost().$this->getRequest()->getBasePath().$this->getServiceLocator()->get('frontHelper')->shopGoodsImage($array['goods_info']->goods_title_image);
        $this->layout()->tongji_goods_class_name = $classInfo->class_name;
        $this->layout()->tongji_goods_brand_name = (isset($array['brand_info']) and isset($array['brand_info']->brand_name)) ? $array['brand_info']->brand_name : '';
        $this->layout()->tongji_goods_stock_state= $array['goods_stock'];
        $this->layout()->tongji_goods_shop_price = $array['goods_info']->goods_shop_price;
        $this->layout()->tongji_goods_price      = $array['goods_info']->goods_price;

        $shareSession = new Container('share');
        $uid = (int) $this->request->getQuery('user_id', 0);
        if($uid > 0 && (!isset($shareSession->distribution_user_id) || $shareSession->distribution_user_id != $uid)) {
            $shareSession->distribution_user_id = $uid;
        }

        if(!defined('FRONT_CACHE_STATE') or FRONT_CACHE_STATE != 'true') {//在没有开启缓存的情况下启用
            //已经购买人数
            $array['order_count']  = $this->getDbshopTable('OrderTable')->countOrder(array('dbshop_order.order_state!=0', 'goods.goods_id='. $goodsId));
            $array['order_count']  = $array['order_count'] + intval($array['goods_info']->virtual_sales);
            //浏览次数+1
            $this->getDbshopTable('GoodsTable')->oneUpdateGoods(array('goods_click'=>($array['goods_info']->goods_click+1)), array('goods_id'=>$goodsId));
            //添加浏览过的商品
            $this->addGoodsViewLog(array('goods_id'=>$goodsId, 'class_id'=>$classId, 'goods_name'=>$array['goods_info']->goods_name, 'price'=>$array['goods_info']->goods_shop_price, 'image'=>$array['goods_info']->goods_thumbnail_image));
        }

        if($array['goods_info']->goods_spec_type == 2) {//规格高级模式
            $view = new ViewModel();
            $view->setTemplate('/shopfront/goods/adv_index.phtml');

            if(!empty($array['goods_info']->adv_spec_group_id)) {
                $array['goods_spec_group_array'] = $this->getDbshopTable('GoodsTagGroupTable')->listTagGroup(array("e.tag_group_id IN (".$array['goods_info']->adv_spec_group_id.")"));

                $goodsSpecTagArray      = $this->getDbshopTable('GoodsAdvSpecGroupTable')->listGoodsAdvSpecGroup(array('goods_id'=>$goodsId));
                $specTagIdArray = array();//用于获取规格具体信息
                if(!empty($goodsSpecTagArray)) {
                    foreach ($goodsSpecTagArray as $specTagValue) {
                        //$array['spec_tag_id'][$specTagValue['group_id']] = explode(',', $specTagValue['selected_tag_id']);
                        $specTagIdArray[] = $specTagValue['selected_tag_id'];
                    }
                }

                //获取已经选中的规格信息
                if(!empty($specTagIdArray)) {
                    $specTagArray = $this->getDbshopTable('GoodsTagTable')->simpleListGoodsTag(array('e.tag_id IN ('.implode(',', $specTagIdArray).')'));
                    if(!empty($specTagArray)) {
                        foreach ($specTagArray as $sTagValue) {
                            $array['spec_tag_id'][$sTagValue['tag_group_id']][] = $sTagValue['tag_id'];

                            $array['spec_tag'][$sTagValue['tag_id']] = $sTagValue;
                        }
                    }
                }
            }

            $view->setVariables($array);
            return $view;
        } else {//规格普通模式
            //颜色和尺寸扩展
            $array['goods_color']       = $this->getDbshopTable('GoodsPriceExtendTable')->infoPriceExtend(array('extend_type'=>'one', 'goods_id'=>$array['goods_info']->goods_id));
            if($array['goods_color']) {
                $array['goods_color_array'] = $this->getDbshopTable('GoodsPriceExtendColorTable')->listPriceExtendColor(array('goods_id'=>$array['goods_info']->goods_id, 'extend_id'=>$array['goods_color']->extend_id));
            }

            $array['goods_size']   = $this->getDbshopTable('GoodsPriceExtendTable')->infoPriceExtend(array('extend_type'=>'two', 'goods_id'=>$array['goods_info']->goods_id));
            if($array['goods_size']) {
                $array['goods_size_array']  = $this->getDbshopTable('GoodsPriceExtendSizeTable')->listPriceExtendSize(array('goods_id'=>$array['goods_info']->goods_id, 'extend_id'=>$array['goods_size']->extend_id));
            }
        }

        return $array;
    }

    /**
     * 获取分享的商品地址
     */
    public function getGoodsShareUrlAction()
    {
        $goodsId = (int) $this->params('goods_id');
        $classId = (int) $this->params('class_id');

        if($goodsId <= 0 or $classId <= 0) exit('');

        $userId = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');

        $url = $this->url()->fromRoute('frontgoods/default', array('action'=>'jumpGoodsUlr', 'goods_id'=>$goodsId, 'class_id'=>$classId), array('force_canonical'=>true, 'query'=>array('user_id'=>$userId)));

        exit($url);
    }

    public function jumpGoodsUlrAction()
    {
        $goodsId = (int) $this->params('goods_id');
        $classId = (int) $this->params('class_id');

        if($goodsId <= 0 or $classId <= 0) exit('');

        //分享记录会员id
        $shareSession = new Container('share');
        $uid = (int) $this->request->getQuery('user_id', 0);
        if($uid > 0 && (!isset($shareSession->distribution_user_id) || $shareSession->distribution_user_id != $uid)) {
            $shareSession->distribution_user_id = $uid;
        }

        return $this->redirect()->toRoute('frontgoods/default', array('goods_id'=>$goodsId, 'class_id'=>$classId), array('query'=>array('user_id'=>$uid)));
    }
    /**
     * 优惠券获取列表
     * @return array
     */
    public function clickCouponAction()
    {
        $this->layout()->title_name = $this->getDbshopLang()->translate('优惠券');

        $array = array();
        $goodsCouponArray = array();

        $userGroup = $this->getDbshopTable('UserGroupTable')->listUserGroup();

        $goodsId    = (int) $this->request->getQuery('goods_id');
        $array['goods_id'] = $goodsId;

        if($goodsId > 0) {
            $goodsInfo  = $this->getDbshopTable('GoodsTable')->infoGoods(array('dbshop_goods.goods_id'=>$goodsId, 'e.language'=>$this->getDbshopLang()->getLocale()));
            if($goodsInfo) {
                //是否有对应商品的优惠券
                $goodsCoupon = $this->getDbshopTable('CouponTable')->couponArray(array('get_coupon_type'=>'click', 'coupon_state'=>1, 'coupon_goods_type'=>'individual_goods', 'coupon_goods_body like \'%"'.$goodsInfo->goods_id.'"%\''));
                if($goodsCoupon) {
                    foreach ($goodsCoupon as $cValue) {
                        $cValue['user_group'] = $this->getUserGroup($cValue, $userGroup);

                        $goodsCouponArray[$cValue['coupon_id']] = $cValue;
                    }
                }
                //是否有对应商品品牌的优惠券
                if($goodsInfo->brand_id) {
                    $goodsCoupon = $this->getDbshopTable('CouponTable')->couponArray(array('get_coupon_type'=>'click', 'coupon_state'=>1, 'coupon_goods_type'=>'brand_goods', 'coupon_goods_body like \'%"'.$goodsInfo->brand_id.'"%\''));
                    if($goodsCoupon) {
                        foreach ($goodsCoupon as $cValue) {
                            $cValue['user_group'] = $this->getUserGroup($cValue, $userGroup);

                            $goodsCouponArray[$cValue['coupon_id']] = $cValue;
                        }
                    }
                }

                $goodsClassIdArray = $this->getDbshopTable('GoodsInClassTable')->listGoodsInClass(array('goods_id'=>$goodsInfo->goods_id));
                if(!empty($goodsClassIdArray)) {
                    $CouponArray = $this->getDbshopTable('CouponTable')->couponArray(array('get_coupon_type'=>'click', 'coupon_state'=>1, 'coupon_goods_type'=>'class_goods'));

                    if($CouponArray) {
                        foreach ($CouponArray as $cValue) {
                            $cArray = unserialize($cValue['coupon_goods_body']);
                            if(!empty($cArray)) {
                                $result = array_intersect($goodsClassIdArray, $cArray);
                                if(!empty($result)) {
                                    $cValue['user_group'] = $this->getUserGroup($cValue, $userGroup);

                                    $goodsCouponArray[$cValue['coupon_id']] = $cValue;
                                }
                            }
                        }
                    }
                }
                $array['goods_coupon'] = $goodsCouponArray;
            }
        }

        $generalCoupon = $this->getDbshopTable('CouponTable')->couponArray(array('get_coupon_type'=>'click', 'coupon_goods_type'=>'all_goods', 'coupon_state'=>1));
        if(!empty($generalCoupon)) {
            foreach ($generalCoupon as $cKey => $cValue) {
                $cValue['user_group'] = $this->getUserGroup($cValue, $userGroup);
                $generalCoupon[$cKey] = $cValue;
            }
        }
        $array['general_coupon'] = $generalCoupon;

        //如果用户登录，这里输出获取过的优惠券
        $userId = (int) $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');
        if($userId > 0) {
            $userCoupon = $this->getDbshopTable('UserCouponTable')->useUserCoupon(array('user_id'=>$userId, 'get_coupon_type'=>'click'));
            $userCouponIdArray = array();
            if(!empty($userCoupon)) {
                foreach ($userCoupon as $uValue) {
                    $userCouponIdArray[] = $uValue['coupon_id'];
                }
            }
            $array['user_coupon_id_array'] = $userCouponIdArray;
        }

        return $array;
    }
    /**
     * 在上面的方法中使用，获取对应的会员组
     * @param $cValue
     * @param $userGroup
     * @return array
     */
    private function getUserGroup($cValue, $userGroup)
    {
        $userGroupArray = array();
        if($cValue['get_user_type'] == 'user_group') {
            $getUserGroup = unserialize($cValue['get_user_group']);
            if(!empty($getUserGroup)) {
                foreach ($userGroup as $value) {
                    if(in_array($value['group_id'], $getUserGroup)) $userGroupArray[$value['group_id']] = $value['group_name'];
                }
            }
        }
        return $userGroupArray;
    }
    /**
     * 优惠券领取操作
     */
    public function clickGetCouponAction()
    {
        $userId         = (int) $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');
        $userGroupId    = (int) $this->getServiceLocator()->get('frontHelper')->getUserSession('group_id');

        if($userId <= 0) exit(json_encode(array('state'=>'false', 'message'=>$this->getDbshopLang()->translate('登录后，才能进行领取！'))));

        $goodsId    = (int) $this->request->getPost('goods_id');
        $couponId   = (int) $this->request->getPost('coupon_id');

        $couponInfo = $this->getDbshopTable('CouponTable')->infoCoupon(array('get_coupon_type'=>'click', 'coupon_state'=>1, 'coupon_id'=>$couponId));
        if($couponInfo) {
            $goodsInfo  = $this->getDbshopTable('GoodsTable')->infoGoods(array('dbshop_goods.goods_id'=>$goodsId, 'e.language'=>$this->getDbshopLang()->getLocale()));

            if($couponInfo->get_user_type == 'user_group') {//检查会员组是否有资格获取
                $couponUserGroup = unserialize($couponInfo->get_user_group);
                if(empty($couponUserGroup)) exit(json_encode(array('state'=>'false', 'message'=>$this->getDbshopLang()->translate('您的等级不在获取该优惠券的范围内！'))));
                elseif (!in_array($userGroupId, $couponUserGroup)) exit(json_encode(array('state'=>'false', 'message'=>$this->getDbshopLang()->translate('您的等级不在获取该优惠券的范围内！'))));
            }

            $nowTime = time();
            if(!empty($couponInfo->get_coupon_start_time) and $nowTime < $couponInfo->get_coupon_start_time) exit(json_encode(array('state'=>'false', 'message'=>$this->getDbshopLang()->translate('获取时间未到！'))));
            if(!empty($couponInfo->get_coupon_end_time) and $nowTime > $couponInfo->get_coupon_end_time) exit(json_encode(array('state'=>'false', 'message'=>$this->getDbshopLang()->translate('获取时间已过！'))));

            //检查是否已经获取过了
            $userCoupon = $this->getDbshopTable('UserCouponTable')->infoUserCoupon(array('coupon_id'=>$couponId, 'user_id'=>$userId));
            if($userCoupon) exit(json_encode(array('state'=>'false', 'message'=>$this->getDbshopLang()->translate('您已经获取过该优惠券，不能重复获取！'))));

            $addUserCoupon = array(
                'coupon_use_state'  => 1,           //优惠券状态
                'user_id'           => $userId,     //会员id
                'user_name'         => $this->getServiceLocator()->get('frontHelper')->getUserSession('user_name'),  //会员名称
                'get_time'          => time(),      //获取时间
                'coupon_id'         => $couponId,   //优惠券id
                'coupon_name'       => $couponInfo->coupon_name,   //优惠券名称
                'coupon_info'       => $couponInfo->coupon_info,//优惠券描述
            );
            if(!empty($couponInfo->coupon_start_time)) $addUserCoupon['coupon_start_use_time'] = $couponInfo->coupon_start_time;
            if(!empty($couponInfo->coupon_end_time)) $addUserCoupon['coupon_expire_time'] = $couponInfo->coupon_end_time;
            $this->getDbshopTable('UserCouponTable')->addUserCoupon($addUserCoupon);
            exit(json_encode(array('state'=>'true', 'message'=>$this->getDbshopLang()->translate('优惠券获取成功！'))));
        }
        exit(json_encode(array('state'=>'false', 'message'=>$this->getDbshopLang()->translate('无优惠券领取！'))));
    }
    /**
     * 获取商品库存
     */
    public function goodsStockAction ()
    {
        $stockNum = '';
        $goodsId = (int) $this->params('goods_id');
        $groupPriceStr = '';
        if($this->request->isPost() and $goodsId != 0) {
            $goodsArray  = $this->request->getPost()->toArray();
            $goodsInfo   = $this->getDbshopTable('GoodsTable')->oneGoodsInfo(array('goods_id'=>$goodsId));
            
            $goodsItem   = $goodsInfo->goods_item;//商品货号

            if($goodsInfo->goods_spec_type == 2) {//高级规格模式
                $specTagIdArray = explode(',', $goodsArray['spec_tag_id_str']);
                sort($specTagIdArray);
                $specTagIdStr = implode(',', $specTagIdArray);
                $extendGoods = $this->getDbshopTable('GoodsPriceExtendGoodsTable')->InfoPriceExtendGoods(array('goods_id'=>$goodsId, 'adv_spec_tag_id'=>$specTagIdStr));
                if(empty($extendGoods)) exit();

                $goodsItem = $extendGoods->goods_extend_item;//商品货号，如果规格存在，覆盖之前值

                //库存显示处理
                $stockNum      = $extendGoods->goods_extend_stock;//默认库存
                $stock_state_id       = '';
                if ($stockNum <= $goodsInfo->goods_out_of_stock_set) {//当库存达到缺货时
                    $stock_state_id = $goodsInfo->goods_out_stock_state;
                } elseif($goodsInfo->goods_stock_state_open == 1) {//当启用库存状态显示，且库存充足
                    $stock_state_id = $goodsInfo->goods_stock_state;
                }
                if($stock_state_id != '') {//说明有文字库存显示，替换默认库存
                    $stockStateInfo = $this->getDbshopTable('StockStateTable')->infoStockState(array('e.stock_state_id'=>$stock_state_id, 'e.language'=>$this->getDbshopLang()->getLocale()));
                    $stockNum       = $stockStateInfo->stock_state_name;
                }
                //获取会员组价格
                $groupPrice = $this->getDbshopTable('GoodsUsergroupPriceTable')->listGoodsUsergroupPrice(array('goods_id'=>$goodsId, 'adv_spec_tag_id'=>$specTagIdStr));
                if(is_array($groupPrice) and !empty($groupPrice)) {
                    foreach($groupPrice as $groupPriceValue) {
                        if($groupPriceValue['goods_user_group_price'] > 0) $groupPriceStr .= $groupPriceValue['group_name'] . '<strong style="color:#ED145B;">' . $this->getServiceLocator()->get('frontHelper')->shopPriceExtend($groupPriceValue['goods_user_group_price']) . '</strong>&nbsp;';
                    }
                }
                //判断是否登录，是否有会员组价格
                $userGroupId = $this->getServiceLocator()->get('frontHelper')->getUserSession('group_id');
                if($userGroupId > 0) {
                    $userGroupPrice = $this->getDbshopTable('GoodsUsergroupPriceTable')->infoGoodsUsergroupPrice(array('goods_id'=>$goodsId, 'user_group_id'=>$userGroupId, 'adv_spec_tag_id'=>$specTagIdStr));
                    if(isset($userGroupPrice->goods_user_group_price) and $userGroupPrice->goods_user_group_price > 0) $extendGoods->goods_extend_price = $userGroupPrice->goods_user_group_price;
                }
                $goodsPrice    = $this->getServiceLocator()->get('frontHelper')->shopPriceExtend($extendGoods->goods_extend_price);

                if($goodsInfo->goods_price > 0)
                    $goodsDiscount = ($this->getServiceLocator()->get('frontHelper')->shopPriceExtend($goodsInfo->goods_price-$extendGoods->goods_extend_price)) . '(' . round($extendGoods->goods_extend_price/$goodsInfo->goods_price*10, 2) . $this->getDbshopLang()->translate('折') . ')';
                else
                    $goodsDiscount = '';

            } else {//普通规格模式
                $extendGoods = $this->getDbshopTable('GoodsPriceExtendGoodsTable')->InfoPriceExtendGoods(array('goods_id'=>$goodsId, 'goods_color'=>$goodsArray['goods_color'], 'goods_size'=>$goodsArray['goods_size']));
                if($extendGoods) {
                    $goodsItem = $extendGoods->goods_extend_item;//商品货号，如果规格存在，覆盖之前值

                    //库存显示处理
                    $stockNum      = $extendGoods->goods_extend_stock;//默认库存
                    $stock_state_id       = '';
                    if ($stockNum <= $goodsInfo->goods_out_of_stock_set) {//当库存达到缺货时
                        $stock_state_id = $goodsInfo->goods_out_stock_state;
                    } elseif($goodsInfo->goods_stock_state_open == 1) {//当启用库存状态显示，且库存充足
                        $stock_state_id = $goodsInfo->goods_stock_state;
                    }
                    if($stock_state_id != '') {//说明有文字库存显示，替换默认库存
                        $stockStateInfo = $this->getDbshopTable('StockStateTable')->infoStockState(array('e.stock_state_id'=>$stock_state_id, 'e.language'=>$this->getDbshopLang()->getLocale()));
                        $stockNum       = $stockStateInfo->stock_state_name;
                    }

                    //获取会员组价格
                    $groupPrice = $this->getDbshopTable('GoodsUsergroupPriceTable')->listGoodsUsergroupPrice(array('goods_id'=>$goodsId, 'goods_color'=>$goodsArray['goods_color'], 'goods_size'=>$goodsArray['goods_size']));
                    if(is_array($groupPrice) and !empty($groupPrice)) {
                        foreach($groupPrice as $groupPriceValue) {
                            if($groupPriceValue['goods_user_group_price'] > 0) $groupPriceStr .= $groupPriceValue['group_name'] . '<strong style="color:#ED145B;">' . $this->getServiceLocator()->get('frontHelper')->shopPriceExtend($groupPriceValue['goods_user_group_price']) . '</strong>&nbsp;';
                        }
                    }

                    //判断是否登录，是否有会员组价格
                    $userGroupId = $this->getServiceLocator()->get('frontHelper')->getUserSession('group_id');
                    if($userGroupId > 0) {
                        $userGroupPrice = $this->getDbshopTable('GoodsUsergroupPriceTable')->infoGoodsUsergroupPrice(array('goods_id'=>$goodsId, 'user_group_id'=>$userGroupId, 'goods_color'=>$goodsArray['goods_color'], 'goods_size'=>$goodsArray['goods_size']));
                        if(isset($userGroupPrice->goods_user_group_price) and $userGroupPrice->goods_user_group_price > 0) $extendGoods->goods_extend_price = $userGroupPrice->goods_user_group_price;
                    }
                    $goodsPrice    = $this->getServiceLocator()->get('frontHelper')->shopPriceExtend($extendGoods->goods_extend_price);

                    if($goodsInfo->goods_price > 0)
                        $goodsDiscount = ($this->getServiceLocator()->get('frontHelper')->shopPriceExtend($goodsInfo->goods_price-$extendGoods->goods_extend_price)) . '(' . round($extendGoods->goods_extend_price/$goodsInfo->goods_price*10, 2) . $this->getDbshopLang()->translate('折') . ')';
                    else
                        $goodsDiscount = '';
                }
            }
            
        }
        echo json_encode(array('stock'=>$stockNum, 'price'=>$goodsPrice, 'discount'=>$goodsDiscount, 'goods_item'=>$goodsItem, 'goods_integral'=>$extendGoods->goods_extend_integral, 'group_price_str'=>$groupPriceStr));
        exit();
    }
    /** 
     * 商品评价列表ajax
     */
    public function listCommentAction()
    {
        $view = new ViewModel();
        $view->setTerminal(true);
        
        $array = array();
        $array['goods_id'] = (int)$this->request->getQuery('goods_id');
        $array['class_id'] = (int)$this->request->getQuery('class_id');
        //商品评价
        $page = $this->params('page',1);
        $array['goods_comment'] = $this->getDbshopTable('GoodsCommentTable')->listGoodsComment(array('page'=>$page, 'page_num'=>16), array('goods_id'=>$array['goods_id'], 'comment_show_state'=>1), true);
        
        return $view->setVariables($array);
    }
    /** 
     * 商品咨询保存
     */
    public function addAskAction()
    {
        if($this->request->isPost()) {
            $postArray = $this->request->getPost()->toArray();
            $postArray['goods_id']          = (int) $postArray['goods_id'];
            $postArray['goods_ask_content'] = trim($postArray['goods_ask_content']);
            
            if($postArray['goods_id'] == 0 or empty($postArray['goods_ask_content'])) exit($this->getDbshopLang()->translate('不是有效的商品信息，无法提交商品咨询'));

            //判断是否开启验证码功能
            if($this->getServiceLocator()->get('frontHelper')->websiteCaptchaState('goods_ask_captcha') == 'true') {
                $captchaSession = new Container('captcha');
                if(strtolower($postArray['captcha_code']) != strtolower($captchaSession->word)) exit($this->getDbshopLang()->translate('验证码错误，请重新提交！'));
            }

            //保存商品咨询信息
            $postArray['ask_content']    = $postArray['goods_ask_content'];
            $postArray['ask_time']       = time();
            $postArray['ask_writer']     = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_name')=='' ? $this->getDbshopLang()->translate('游客') : $this->getServiceLocator()->get('frontHelper')->getUserSession('user_name');
            $postArray['ask_show_state'] = 0;
            if(!$this->getDbshopTable('GoodsAskTable')->addGoodsAsk($postArray)) exit($this->getDbshopLang()->translate('商品咨询保存不成功'));
            echo 'true';

            /*===========================商品咨询提醒邮件发送,必须是登陆用户才可以有此服务===========================*/
            $buyerEmail = $this->getServiceLocator()->get('frontHelper')->getSendMessageBuyerEmail('goods_ask_state', $this->getServiceLocator()->get('frontHelper')->getUserSession('user_email'));
            $adminEmail = $this->getServiceLocator()->get('frontHelper')->getSendMessageAdminEmail('goods_ask_state');
            if($this->getServiceLocator()->get('frontHelper')->getUserSession('user_name') != '' and ($buyerEmail != '' or $adminEmail != '')) {
                $sendMessageBody = $this->getServiceLocator()->get('frontHelper')->getSendMessageBody('goods_ask');
                if($sendMessageBody != '') {
                    $sendArray = array();
                    $sendArray['shopname']     = $this->getServiceLocator()->get('frontHelper')->websiteInfo('shop_name');
                    $sendArray['shopurl']      = $this->getServiceLocator()->get('frontHelper')->dbshopHttpOrHttps() . $this->getServiceLocator()->get('frontHelper')->dbshopHttpHost() . $this->url()->fromRoute('shopfront/default');
                    $sendArray['askusername']  = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_name');
                    $sendArray['asktime']      = $postArray['ask_time'];

                    $goodsInfo   = $this->getDbshopTable('GoodsTable')->infoGoods(array('dbshop_goods.goods_id'=>$postArray['goods_id'], 'e.language'=>$this->getDbshopLang()->getLocale()));
                    $inClass     = $this->getDbshopTable('GoodsInClassTable')->oneGoodsInClass(array('dbshop_goods_in_class.goods_id'=>$postArray['goods_id'], 'c.class_state'=>1));
                    $sendArray['goodsname']  = '<a href="'. $this->getServiceLocator()->get('frontHelper')->dbshopHttpOrHttps() . $this->getServiceLocator()->get('frontHelper')->dbshopHttpHost() . $this->url()->fromRoute('frontgoods/default', array('goods_id'=>$postArray['goods_id'], 'class_id'=>$inClass[0]['class_id'])).'" target="_blank">' . $goodsInfo->goods_name . '</a>';

                    $sendArray['subject']       = $sendArray['shopname'] . '|' . $this->getDbshopLang()->translate('新的商品咨询') . '|' . $goodsInfo->goods_name;
                    $sendArray['send_mail'][]   = $buyerEmail;
                    $sendArray['send_mail'][]   = $adminEmail;
                    $sendMessageBody            = $this->getServiceLocator()->get('frontHelper')->createSendMessageContent($sendArray, $sendMessageBody);
                    try {
                        $sendState = $this->getServiceLocator()->get('shop_send_mail')->SendMesssageMail($sendArray, $sendMessageBody);
                        $sendState = ($sendState ? 1 : 2);
                    } catch (\Exception $e) {
                        $sendState = 2;
                    }
                    //记录给用户发的电邮
                    if($sendArray['send_mail'][0] != '') {
                        $sendLog = array(
                            'mail_subject' => $sendArray['subject'],
                            'mail_body'    => $sendMessageBody,
                            'send_time'    => time(),
                            'user_id'      => $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id'),
                            'send_state'   => $sendState
                        );
                        $this->getDbshopTable('UserMailLogTable')->addUserMailLog($sendLog);
                    }
                }
            }
            /*===========================商品咨询提醒邮件发送,必须是登陆用户才可以有此服务===========================*/

            exit();
            
            //保存商品咨询基础表信息
            /*
             * 暂时不使用，以后需要时再次使用
            $askCount = $this->getDbshopTable('GoodsAskTable')->countGoodsAsk(array('goods_id'=>$postArray['goods_id']));
            $askCount = $askCount == 0 ? 1 : $askCount;
            $askBaseArray = array();
            $askBaseArray['goods_id']        = $postArray['goods_id'];
            $askBaseArray['ask_last_writer'] = $postArray['ask_writer'];
            $askBaseArray['ask_last_time']   = $postArray['ask_time'];
            $askBaseArray['ask_count']       = $askCount;
            $askBaseInfo  = $this->getDbshopTable('GoodsAskBaseTable')->infoGoodsAskBase(array('goods_id'=>$postArray['goods_id']));
            if($askBaseInfo) {
                $this->getDbshopTable('GoodsAskBaseTable')->updateGoodsAskBase($askBaseArray, array('goods_id'=>$postArray['goods_id']));
            } else {
                $this->getDbshopTable('GoodsAskBaseTable')->addGoodsAskBase($askBaseArray);
            }*/
        }
        exit($this->getDbshopLang()->translate('商品咨询保存不成功'));
    }
    /** 
     * 商品咨询列表ajax
     * @return Ambigous <\Zend\View\Model\ViewModel, \Zend\View\Model\ViewModel>
     */
    public function goodsAskAction()
    {
        $view = new ViewModel();
        $view->setTerminal(true);
        
        $array = array();
        $array['goods_id'] = (int)$this->request->getQuery('goods_id');
        $array['class_id'] = (int)$this->request->getQuery('class_id');
        //商品咨询
        $askPage = $this->params('page',1);
        $array['goods_ask_list'] = $this->getDbshopTable('GoodsAskTable')->listGoodsAsk(array('page'=>$askPage, 'page_num'=>16), array('dbshop_goods_ask.ask_show_state'=>1, 'dbshop_goods_ask.goods_id'=>$array['goods_id'], 'e.language'=>$this->getDbshopLang()->getLocale()));

        return $view->setVariables($array);
    }
    /**
     * ajax获取浏览历史，同时添加浏览过的商品，及查看次数相加，在开启缓存时启用
     */
    public function browserhistoryAction()
    {
        $view = new ViewModel();
        $view->setTerminal(true);

        $array = array();
        $array['goods_id'] = (int)$this->request->getQuery('goods_id');
        $array['class_id'] = (int)$this->request->getQuery('class_id');
        //商品基本信息
        $array['goods_info']   = $this->getDbshopTable('GoodsTable')->infoGoods(array('dbshop_goods.goods_id'=>$array['goods_id'], 'goods_state'=>1, 'e.language'=>$this->getDbshopLang()->getLocale()));
        if(!empty($array['goods_info'])) {
            //浏览次数+1
            $this->getDbshopTable('GoodsTable')->oneUpdateGoods(array('goods_click'=>($array['goods_info']->goods_click+1)), array('goods_id'=>$array['goods_id']));
            //添加浏览过的商品
            $this->addGoodsViewLog(array('goods_id'=>$array['goods_id'], 'class_id'=>$array['class_id'], 'goods_name'=>$array['goods_info']->goods_name, 'price'=>$array['goods_info']->goods_shop_price, 'image'=>$array['goods_info']->goods_thumbnail_image));
        }

        return $view->setVariables($array);
    }
    /**
     * ajax获取库存，用于开启缓存下使用
     */
    public function ajaxgoodsStockAction()
    {
        $array = array();
        $array['goods_id'] = (int)$this->request->getQuery('goods_id');
        $array['class_id'] = (int)$this->request->getQuery('class_id');
        //商品基本信息
        $array['goods_info']   = $this->getDbshopTable('GoodsTable')->infoGoods(array('dbshop_goods.goods_id'=>$array['goods_id'], 'goods_state'=>1, 'e.language'=>$this->getDbshopLang()->getLocale()));

        //商品库存显示
        $array['goods_stock'] = $array['goods_info']->goods_stock;//默认库存数
        $stock_state_id       = '';
        if ($array['goods_info']->goods_stock <= $array['goods_info']->goods_out_of_stock_set) {//当库存达到缺货时
            $stock_state_id = $array['goods_info']->goods_out_stock_state;
        } elseif($array['goods_info']->goods_stock_state_open == 1) {//当启用库存状态显示，且库存充足
            $stock_state_id = $array['goods_info']->goods_stock_state;
        }
        if($stock_state_id != '') {//说明有文字库存显示，替换默认库存
            $stockStateInfo       = $this->getDbshopTable('StockStateTable')->infoStockState(array('e.stock_state_id'=>$stock_state_id, 'e.language'=>$this->getDbshopLang()->getLocale()));
            $array['goods_stock'] = $stockStateInfo->stock_state_name;
        }

        $array['order_count']  = $this->getDbshopTable('OrderTable')->countOrder(array('dbshop_order.order_state!=0', 'goods.goods_id='. $array['goods_id']));
        $array['order_count']  = $array['order_count'] + intval($array['goods_info']->virtual_sales);

        echo json_encode(array('order_count'=>$array['order_count'], 'goods_stock'=>$array['goods_stock']));
        exit();
    }
    //添加浏览过的商品，默认是六个商品
    private function addGoodsViewLog(array $goodsArray)
    {
        $array[$goodsArray['goods_id']] = $goodsArray;
        if(isset($_COOKIE['view_goods'])) {
            $array = unserialize($_COOKIE['view_goods']);
            if(!isset($array[$goodsArray['goods_id']])) {
                if(count($array) >= 6) unset($array[key($array)]);
                $array[$goodsArray['goods_id']] = $goodsArray;
                setcookie('view_goods', serialize($array), time()+60*60*24*10, '/');
            }
        } else {
            setcookie('view_goods', serialize($array), time()+60*60*24*10, '/');
        }
    }
    /** 
     * 获取商品对应的属性数组
     * @param unknown $attributeGroupId
     * @param unknown $goodsId
     * @return multitype:string
     */
    private function getAttributeArray($attributeGroupId, $goodsId)
    {
        $attributeArray      = $this->getDbshopTable('GoodsAttributeTable')->listAttribute(array('dbshop_goods_attribute.attribute_group_id'=>$attributeGroupId, 'e.language'=>$this->getDbshopLang()->getLocale()));
        $attributeValueArray = $this->getDbshopTable('GoodsAttributeValueTable')->listAttributeValue(array('dbshop_goods_attribute_value.attribute_group_id'=>$attributeGroupId, 'e.language'=>$this->getDbshopLang()->getLocale()));
        $valueArray = array();
        if(is_array($attributeValueArray) and !empty($attributeValueArray)) {
            foreach ($attributeValueArray as $v_value) {
                $valueArray[$v_value['attribute_id']][$v_value['value_id']] = $v_value['value_name'];
            }
        }
        
        //获取已经插入商品中的属性值
        $goodsInAttribute = array();
        if($goodsId != '') {
            $goodsAttribute = $this->getDbshopTable('GoodsInAttributeTable')->listGoodsInAttribute(array('goods_id'=>$goodsId));
            if(is_array($goodsAttribute) and !empty($goodsAttribute)) {
                foreach ($goodsAttribute as $gA_value) {
                    $goodsInAttribute[$gA_value['attribute_id']] = $gA_value['attribute_body'];
                }
            }
        }
        
        $array = array();
        if(is_array($attributeArray) and !empty($attributeArray)) {
            foreach ($attributeArray as $a_value) {
                if(isset($goodsInAttribute[$a_value['attribute_id']]) and !empty($goodsInAttribute[$a_value['attribute_id']])) {
                switch ($a_value['attribute_type']) {
                    case 'select'://下拉菜单
                    case 'radio'://单选菜单
                        $array[] = '<strong>' .$a_value['attribute_name']. '：</strong>' . $valueArray[$a_value['attribute_id']][$goodsInAttribute[$a_value['attribute_id']]];
                        break;
                    case 'checkbox'://复选菜单
                        $checkboxChecked = explode(',', $goodsInAttribute[$a_value['attribute_id']]);
                        $checkboxV       = '';
                        foreach ($checkboxChecked as $valueId) {
                            $checkboxV .= $valueArray[$a_value['attribute_id']][$valueId] . ' , ';
                        }
                        $array[] = '<strong>' .$a_value['attribute_name']. '：</strong>' . substr($checkboxV, 0, -2);
                        break;
                    case 'input'://输入表单
                    case 'textarea'://文本域表单
                        $array[] = '<strong>' .$a_value['attribute_name']. '：</strong>' . $goodsInAttribute[$a_value['attribute_id']];
                        break;
                    }
                }
            }
        }
        
        return $array;
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
