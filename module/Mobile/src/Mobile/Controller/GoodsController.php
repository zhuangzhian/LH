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

use User\Service\JssdkService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;

class GoodsController extends AbstractActionController
{
    private $dbTables = array();
    private $translator;

    public function indexAction()
    {
        $array = array();

        $goodsId = (int) $this->params('goods_id');
        $classId = (int) $this->params('class_id');

        if($goodsId <= 0 or $classId <= 0) return $this->redirect()->toRoute('mobile/default');

        //判断是否为手机端访问
        if(!$this->getServiceLocator()->get('frontHelper')->isMobile()) return $this->redirect()->toRoute('frontgoods/default', array('goods_id'=>$goodsId, 'class_id'=>$classId));

        $array['class_id']   = $classId;

        $this->layout()->dbTongJiPage = 'goods_body';
        $this->layout()->mobile_title_name = $this->getDbshopLang()->translate('商品详情');

        //商品基本信息
        $array['goods_info']   = $this->getDbshopTable('GoodsTable')->infoGoods(array('dbshop_goods.goods_id'=>$goodsId, 'e.language'=>$this->getDbshopLang()->getLocale()));
        if(!$array['goods_info']) return $this->redirect()->toRoute('mobile/default');

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

        //商品图片
        $array['goods_images'] = $this->getDbshopTable('GoodsImageTable')->listImage(array('goods_id='.$goodsId, 'image_slide=1'))->toArray();
        //关联商品
        $array['relation_goods_array'] = $this->getDbshopTable('GoodsRelationTable')->listRelationGoods(array('dbshop_goods_relation.goods_id'=>$goodsId, 'g.goods_state'=>1, 'e.language'=>$this->getDbshopLang()->getLocale()), array("dbshop_goods_relation.relation_sort ASC"));

        //商品自定义信息
        $array['goods_custom'] = $this->getDbshopTable('GoodsCustomTable')->listGoodsCustom(array('goods_id'=>$goodsId, 'custom_content_state'=>1));

        //商品品牌
        if($array['goods_info']->brand_id != '') $array['brand_info'] = $this->getDbshopTable('GoodsBrandTable')->infoBrand(array('e.brand_id'=>$array['goods_info']->brand_id, 'e.language'=>$this->getDbshopLang()->getLocale()));

        //商品销量
        $array['order_count']  = $this->getDbshopTable('OrderGoodsTable')->countOrderGoodsNum(array('o.order_state!=0', 'dbshop_order_goods.goods_id='. $goodsId));
        $array['order_count']  = $array['order_count'] + intval($array['goods_info']->virtual_sales);
        //商品属性
        if($array['goods_info']->attribute_group_id != '') {
            $array['goods_attribute'] = $this->getAttributeArray($array['goods_info']->attribute_group_id, $goodsId);
        }

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

        //顶部title使用
        $this->layout()->title_name         = $array['goods_info']->goods_name;
        $this->layout()->extend_title_name  = $array['goods_info']->goods_extend_name;
        $this->layout()->extend_keywords    = $array['goods_info']->goods_keywords;
        $this->layout()->extend_description = $array['goods_info']->goods_description;

        //检查是否已经被收藏
        $array['favorites_state'] = 'false';
        if($this->getServiceLocator()->get('frontHelper')->getUserSession('user_id') != '') {
            $favoritesInfo = $this->getDbshopTable('UserFavoritesTable')->infoFavorites(array('goods_id'=>$goodsId, 'user_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')));
            if($favoritesInfo) $array['favorites_state'] = 'true';
        }

        $shareSession = new Container('share');
        if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
            //分享设置信息
            $jsSDK = new JssdkService();
            $array['signPackage'] = $jsSDK->getSignPackage();
            $array['shareMd5Str'] = md5(time().$array['goods_info']->goods_name);
            $shareSession = new Container('share');
            $shareSession->shareMd5Str = $array['shareMd5Str'];
        }

        $uid = (int) $this->request->getQuery('user_id', 0);
        if($uid > 0 && (!isset($shareSession->distribution_user_id) || $shareSession->distribution_user_id != $uid)) {
            $shareSession->distribution_user_id = $uid;
        }

        if($array['goods_info']->goods_spec_type == 2) {//规格高级模式
            $view = new ViewModel();
            $view->setTemplate('/mobile/goods/adv_index.phtml');

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
        } else {
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
     * 商品分享
     * @return array|\Zend\Http\Response
     */
    public function goodsShareAction()
    {
        $array = array();

        $goodsId = (int) $this->params('goods_id');
        $classId = (int) $this->params('class_id');

        if($goodsId <= 0 or $classId <= 0) return $this->redirect()->toRoute('mobile/default');

        //商品基本信息
        $array['goods_info']   = $this->getDbshopTable('GoodsTable')->infoGoods(array('dbshop_goods.goods_id'=>$goodsId, 'e.language'=>$this->getDbshopLang()->getLocale()));
        if(!$array['goods_info']) return $this->redirect()->toRoute('mobile/default');

        $this->layout()->title_name = $this->getDbshopLang()->translate('商品分享');;

        $userId = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');
        $goodsUrl = $this->url()->fromRoute('m_goods/default', array('goods_id'=>$goodsId, 'class_id'=>$classId), array('force_canonical'=>true, 'query'=>array('user_id'=>$userId)));

        $qrCodePath = '/public/upload/user/shareGoods/';
        $fileName   = ($userId > 0 ? $userId . '_' : ''). $array['goods_info']->goods_id . '.png';
        if(file_exists(DBSHOP_PATH . $qrCodePath . $fileName)) @unlink(DBSHOP_PATH . $qrCodePath . $fileName);
        if(!is_dir(DBSHOP_PATH . $qrCodePath)) @mkdir(DBSHOP_PATH . $qrCodePath, 0755, true);
        include DBSHOP_PATH . '/module/Upload/src/Upload/Plugin/Phpqrcode/phpqrcode.php';

        \QRcode::png($goodsUrl, DBSHOP_PATH . $qrCodePath . $fileName, QR_ECLEVEL_L, 6, 1);
        $array['goods_qrcode'] = DBSHOP_PATH . $qrCodePath . $fileName;

        $array['user_id'] = $userId;
        $array['class_id']  = $classId;
        $array['goods_id']  = $goodsId;

        if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
            //分享设置信息
            $jsSDK = new JssdkService();
            $array['signPackage'] = $jsSDK->getSignPackage();
        }

        return $array;
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
     * 分享返回
     */
    public function shareReturnAction()
    {
        $userId = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');
        if($userId >= 0) {//登录用户
            $goodsId    = (int)$this->request->getPost('goods_id');
            $md5Str     = $this->request->getPost('md5_str');
            $shareType  = $this->request->getPost('type');

            $shareTypeTitleArray = array(
                'Timeline'      =>'微信朋友圈分享',
                'AppMessage'    => '微信朋友分享',
                'QQ'            => 'QQ分享',
                'Weibo'         => '腾讯微博分享',
                'QZone'         => 'QQ空间分享'
            );

            $shareSession = new Container('share');
            if(empty($md5Str) or empty($shareSession->shareMd5Str)) exit();
            if($md5Str != $shareSession->shareMd5Str) exit();

            $goodsInfo = $this->getDbshopTable('GoodsTable')->infoGoods(array('dbshop_goods.goods_id'=>$goodsId, 'e.language'=>$this->getDbshopLang()->getLocale()));
            if(!$goodsInfo) exit();

            $message = '分享成功';
            exit(json_encode(array('state'=>'true', 'message'=>$message)));
        } else {//非登录用户
            exit();
        }
    }

    public function goodsAskAction()
    {
        $view = new ViewModel();
        $view->setTerminal(true);

        $array = array();
        $array['goods_id'] = (int)$this->request->getQuery('goods_id');
        $array['class_id'] = (int)$this->request->getQuery('class_id');
        //商品咨询
        $askPage = $this->params('page',1);
        $array['goods_ask_list'] = $this->getDbshopTable('GoodsAskTable')->listGoodsAsk(array('page'=>$askPage, 'page_num'=>10), array('dbshop_goods_ask.ask_show_state'=>1, 'dbshop_goods_ask.goods_id'=>$array['goods_id'], 'e.language'=>$this->getDbshopLang()->getLocale()));

        return $view->setVariables($array);
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
        $array['goods_comment'] = $this->getDbshopTable('GoodsCommentTable')->listGoodsComment(array('page'=>$page, 'page_num'=>10), array('goods_id'=>$array['goods_id'], 'comment_show_state'=>1), true);

        return $view->setVariables($array);
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