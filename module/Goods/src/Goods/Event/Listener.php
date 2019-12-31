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

namespace Goods\Event;

use Zend\EventManager\Event;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;

class Listener implements ListenerAggregateInterface
{
    protected $listeners = array();

    /**
     * @param EventManagerInterface $events
     */
    public function attach(EventManagerInterface $events)
    {
        $shareEvents = $events->getSharedManager();

        //后台
        $this->listeners[] = $shareEvents->attach('Goods\Controller\GoodsController', 'goods.save.backstage.post',
            array($this, 'onSaveGoodsUsergroupPrice')
        );
        $this->listeners[] = $shareEvents->attach('Goods\Controller\GoodsController', 'goods.info.backstage.post',
            array($this, 'onShowGoodsUsergroupPirce')
        );
        $this->listeners[] = $shareEvents->attach('Goods\Controller\GoodsController', 'goods.del.backstage.post',
            array($this, 'onDelGoodsUsergroupPirce')
        );
        //前台
        $this->listeners[] = $shareEvents->attach(
            array(
                'Shopfront\Controller\GoodsController',
                'Mobile\Controller\GoodsController',
                'Dbapi\Controller\JsonapiController',
            ),
            'goods.info.front.post',
            array($this, 'onShowFrontGoodsUsergroupPirce')
        );

        //前台商品优惠券
        $this->listeners[] = $shareEvents->attach(
            array(
                'Shopfront\Controller\GoodsController',
                'Mobile\Controller\GoodsController',
                'Dbapi\Controller\JsonapiController',
            ),
            'goods.info.front.post',
            array($this, 'onShowFrontClickCoupon')
        );

        $this->listeners[] = $shareEvents->attach(
            array(
                'Shopfront\Controller\UserController',
                'Mobile\Controller\UserController'
            ),
            'user.login.front.post',
            array($this, 'onChangeFrontCartUsergroupPirce'),
            100
        );
        $this->listeners[] = $shareEvents->attach(
            array(
                'Shopfront\Controller\UserController',
                'Mobile\Controller\UserController'
            ),
            'user.register.front.post',
            array($this, 'onChangeFrontCartUsergroupPirce'),
            100
        );

        //前台api
        $this->listeners[] = $shareEvents->attach(
            'Dbapi\Controller\JsonapiController',
            'user.login.front.post',
            array($this, 'onChangeApiCartUsergroupPirce')
        );
        $this->listeners[] = $shareEvents->attach(
            'Dbapi\Controller\JsonapiController',
            'user.register.front.post',
            array($this, 'onChangeApiCartUsergroupPirce')
        );

        //商品缺货通知（手机短信和邮件）
        $this->listeners[] = $shareEvents->attach(
            array(
                'Shopfront\Controller\CartController',
                'Mobile\Controller\CartController',
                'Dbapi\Controller\JsonapiController'
            ),
            'cart.submit.front.post',
            array($this, 'goodsStockMessage')
        );
        //前台付款后，虚拟商品进行短信或者邮件通知账号和密码
        $this->listeners[] = $shareEvents->attach(
            array(
                'Shopfront\Controller\OrderController',
                'Mobile\Controller\OrderController',
                'Mobile\Controller\WxController',
                'Dbapi\Controller\JsonapiController',

                'Dbpingpay\Controller\PaymentController',
                'Dbbeecloud\Controller\PaymentController'
            ),
            'order.pay.front.post',
            array($this, 'onSendVirtualGoods'),
            100
        );
        //后台付款完成，对于虚拟商品的短信和邮件通知（至于库存不够，在发货时再次执行的虚拟商品发货，则不做通知处理）
        $this->listeners[] = $shareEvents->attach(
            array(
                'Orders\Controller\OrdersController'
            ),
            'order.pay.backstage.post',
            array($this, 'onSendVirtualGoods'),
            100
        );

    }
    /**
     * @param EventManagerInterface $events
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }
    /**
     * 保存商品对应的客户组价格
     * @param Event $e
     * @return array
     */
    public function onSaveGoodsUsergroupPrice(Event $e)
    {
        $values     = $e->getParam('values');
        if(isset($values['goods_id']) and $values['goods_id'] > 0) {
            if(is_array($values['user_group_price']) and !empty($values['user_group_price'])) {
                $goodsUsergroupPriceTable = $e->getTarget()->getServiceLocator()->get('GoodsUsergroupPriceTable');
                $goodsUsergroupPriceTable->delGoodsUsergroupPrice(array('goods_id'=>$values['goods_id']));//清空该商品内的会员组价格
                foreach($values['user_group_price'] as $userGroupId => $groupPrice) {
                    $goodsUsergroupPriceTable->addGoodsUsergroupPrice(
                        array(
                            'goods_id'              => $values['goods_id'],
                            'user_group_id'         => $userGroupId,
                            'goods_user_group_price'=> $groupPrice
                        )
                    );
                }

                if($values['goods_spec_type'] == 2) {
                    if(
                        isset($values['spec_tag_id_str'])
                        and !empty($values['spec_tag_id_str']))
                    {
                        foreach ($values['spec_tag_id_str'] as $id_str_value) {
                            foreach ($values['spec_goods_group_price'][$id_str_value] as $uGroupId => $uGroupPrice) {
                                $specTagIdArray = explode('_', ltrim($id_str_value, '_'));
                                sort($specTagIdArray);
                                $goodsUsergroupPriceTable->addGoodsUsergroupPrice(
                                    array(
                                        'goods_id'               => $values['goods_id'],
                                        'user_group_id'          => $uGroupId,
                                        'adv_spec_tag_id'        => implode(',', $specTagIdArray),
                                        'goods_user_group_price' => $uGroupPrice
                                    )
                                );
                            }
                        }
                    }

                } else {
                    //当尺寸和颜色都存在时，进行处理
                    if(
                        isset($values['goods_size_value'])
                        and is_array($values['goods_size_value'])
                        and !empty($values['goods_size_value'])
                        and isset($values['goods_color_value'])
                        and is_array($values['goods_color_value'])
                        and !empty($values['goods_color_value']))
                    {
                        foreach ($values['color_size_color'] as $cSizeKey => $cSizeValue) {
                            foreach($values['color_size_group_price'][$cSizeValue][$values['color_size_size'][$cSizeKey]] as $uGroupId => $uGroupPrice) {
                                $goodsUsergroupPriceTable->addGoodsUsergroupPrice(
                                    array(
                                        'goods_id'               => $values['goods_id'],
                                        'user_group_id'          => $uGroupId,
                                        'goods_color'            => $cSizeValue,
                                        'goods_size'             => $values['color_size_size'][$cSizeKey],
                                        'goods_user_group_price' => $uGroupPrice
                                    )
                                );
                            }
                        }
                    }
                }

            }
        }
    }
    /**
     * 后台显示客户组价格
     * @param Event $e
     * @return array
     */
    public function onShowGoodsUsergroupPirce(Event $e)
    {
        $values = $e->getParam('values');
        $goodsGroupPrice = array();
        $goodsColorSizeGroupPrice = array();
        if(isset($values['goods_id']) and $values['goods_id'] > 0) {
            $goodsUsergroupPriceTable = $e->getTarget()->getServiceLocator()->get('GoodsUsergroupPriceTable');
            $groupPirce = $goodsUsergroupPriceTable->listGoodsUsergroupPrice(array('goods_id'=>$values['goods_id']));
            if(is_array($groupPirce) and !empty($groupPirce)) {
                if($values['goods_info']->goods_spec_type == 2) {
                    foreach($groupPirce as $priceValue) {
                        if(empty($priceValue['adv_spec_tag_id'])) {
                            $goodsGroupPrice[$priceValue['user_group_id']] = $priceValue['goods_user_group_price'];
                        } else {
                            $goodsColorSizeGroupPrice[str_replace(',', '_', $priceValue['adv_spec_tag_id'])][$priceValue['user_group_id']] = $priceValue['goods_user_group_price'];
                        }
                    }
                } else {
                    foreach($groupPirce as $priceValue) {
                        if(empty($priceValue['goods_color']) and empty($priceValue['goods_size'])) {
                            $goodsGroupPrice[$priceValue['user_group_id']] = $priceValue['goods_user_group_price'];
                        } else {
                            $goodsColorSizeGroupPrice[$priceValue['goods_color']][$priceValue['goods_size']][$priceValue['user_group_id']] = $priceValue['goods_user_group_price'];
                        }
                    }
                }
            }
        }
        return array('group_price'=>array('one_group_price'=>$goodsGroupPrice, 'color_size_group_price'=>$goodsColorSizeGroupPrice));
    }
    /**
     * 删除客户组价格
     * @param Event $e
     */
    public function onDelGoodsUsergroupPirce(Event $e)
    {
        $values = $e->getParam('values');
        if(!empty($values)) {
            $goodsUsergroupPriceTable = $e->getTarget()->getServiceLocator()->get('GoodsUsergroupPriceTable');
            $goodsUsergroupPriceTable->delGoodsUsergroupPrice(array('goods_id'=>$values));
        }
    }
    /**
     * 前台获取客户组价格
     * @param Event $e
     * @return array
     */
    public function onShowFrontGoodsUsergroupPirce(Event $e)
    {
        $values = $e->getParam('values');
        $userGroupPrice = array();
        if(isset($values['goods_info']->goods_id) and $values['goods_info']->goods_id > 0) {
            $goodsUsergroupPriceTable = $e->getTarget()->getServiceLocator()->get('GoodsUsergroupPriceTable');
            $array = $goodsUsergroupPriceTable->listGoodsUsergroupPrice(array('goods_id'=>$values['goods_info']->goods_id, 'goods_color'=>'', 'goods_size'=>'', 'adv_spec_tag_id'=>'', 'goods_user_group_price > 0'));
            if(is_array($array) and !empty($array)) {
                foreach($array as $value) {
                    $userGroupPrice[] = array('group_name' => $value['group_name'], 'group_price' => $value['goods_user_group_price']);
                }
            }
        }
        return array('group_price'=>$userGroupPrice);
    }
    /**
     * 输出点击获取类型的优惠券
     * @param Event $e
     * @return array
     */
    public function onShowFrontClickCoupon(Event $e)
    {
        $values = $e->getParam('values');

        $couponTable = $e->getTarget()->getServiceLocator()->get('CouponTable');

        $newTime = time();

        //是否有对应商品的优惠券
        $goodsCoupon = $couponTable->couponArray(
            array(
                'get_coupon_type'=>'click',
                'coupon_state'=>1,
                'coupon_goods_type'=>'individual_goods',
                'coupon_goods_body like \'%"'.$values['goods_info']->goods_id.'"%\'',
                "(
                      (get_coupon_start_time IS NULL AND get_coupon_end_time IS NULL)
                      OR
                      (get_coupon_start_time IS NOT NULL AND get_coupon_start_time<={$newTime} AND get_coupon_end_time IS NULL)
                      OR
                      (get_coupon_end_time IS NOT NULL AND get_coupon_end_time>={$newTime} AND get_coupon_start_time IS NULL)
                      OR
                      (get_coupon_end_time IS NOT NULL AND get_coupon_end_time>={$newTime} AND get_coupon_start_time IS NOT NULL AND get_coupon_start_time<={$newTime})
                )"
            ), 3);
        if($goodsCoupon) return array('click_coupon'=>$goodsCoupon);

        //是否有对应商品品牌的优惠券
        if($values['goods_info']->brand_id) {
            $goodsCoupon = $couponTable->couponArray(
                array(
                    'get_coupon_type'=>'click',
                    'coupon_state'=>1,
                    'coupon_goods_type'=>'brand_goods',
                    'coupon_goods_body like \'%"'.$values['goods_info']->brand_id.'"%\'',
                    "(
                      (get_coupon_start_time IS NULL AND get_coupon_end_time IS NULL)
                      OR
                      (get_coupon_start_time IS NOT NULL AND get_coupon_start_time<={$newTime} AND get_coupon_end_time IS NULL)
                      OR
                      (get_coupon_end_time IS NOT NULL AND get_coupon_end_time>={$newTime} AND get_coupon_start_time IS NULL)
                      OR
                      (get_coupon_end_time IS NOT NULL AND get_coupon_end_time>={$newTime} AND get_coupon_start_time IS NOT NULL AND get_coupon_start_time<={$newTime})
                      )"
                ), 3);
            if($goodsCoupon) return array('click_coupon'=>$goodsCoupon);
        }

        //是否有对应商品分类的优惠券
        $goodsInClassTable = $e->getTarget()->getServiceLocator()->get('GoodsInClassTable');
        $goodsClassIdArray = $goodsInClassTable->listGoodsInClass(array('goods_id'=>$values['goods_info']->goods_id));
        if(!empty($goodsClassIdArray)) {
            $goodsCouponArray = $couponTable->couponArray(
                array(
                    'get_coupon_type'=>'click',
                    'coupon_state'=>1,
                    'coupon_goods_type'=>'class_goods',
                    "(
                      (get_coupon_start_time IS NULL AND get_coupon_end_time IS NULL)
                      OR
                      (get_coupon_start_time IS NOT NULL AND get_coupon_start_time<={$newTime} AND get_coupon_end_time IS NULL)
                      OR
                      (get_coupon_end_time IS NOT NULL AND get_coupon_end_time>={$newTime} AND get_coupon_start_time IS NULL)
                      OR
                      (get_coupon_end_time IS NOT NULL AND get_coupon_end_time>={$newTime} AND get_coupon_start_time IS NOT NULL AND get_coupon_start_time<={$newTime})
                    )"
                ));
            $goodsCoupon      = array();
            if($goodsCouponArray) {
                foreach ($goodsCouponArray as $cValue) {
                    $cArray = unserialize($cValue['coupon_goods_body']);
                    if(!empty($cArray)) {
                        $result = array_intersect($goodsClassIdArray, $cArray);
                        if(!empty($result)) $goodsCoupon[] = $cValue;
                    }
                    if(count($goodsCoupon) >= 3) break;
                }
                if($goodsCoupon) return array('click_coupon'=>$goodsCoupon);
            }

        }

        //全品类商品优惠券
        $clickCouponArray = $couponTable->couponArray(
            array(
                'get_coupon_type'=>'click',
                'coupon_state'=>1,
                'coupon_goods_type'=>'all_goods',
                "(
                      (get_coupon_start_time IS NULL AND get_coupon_end_time IS NULL)
                      OR
                      (get_coupon_start_time IS NOT NULL AND get_coupon_start_time<={$newTime} AND get_coupon_end_time IS NULL)
                      OR
                      (get_coupon_end_time IS NOT NULL AND get_coupon_end_time>={$newTime} AND get_coupon_start_time IS NULL)
                      OR
                      (get_coupon_end_time IS NOT NULL AND get_coupon_end_time>={$newTime} AND get_coupon_start_time IS NOT NULL AND get_coupon_start_time<={$newTime})
                )"
            ), 3);

        return array('click_coupon'=>$clickCouponArray);
    }
    /**
     * 会员注册和登录的时候，如果之前购物车有信息，则进行会员价格的判断处理（登陆后的购物车商品价格如果存在会员价格，修改为会员价格）
     * @param Event $e
     */
    public function onChangeFrontCartUsergroupPirce(Event $e)
    {
        $values     = $e->getParam('values');
        $cartArray  = $e->getTarget()->getServiceLocator()->get('frontHelper')->getCartSession();
        if(isset($values['group_id']) and $values['group_id'] > 0 and !empty($cartArray)) {
            $goodsPreferentialPrice = array();
            foreach($cartArray as $key => $value) {
                if(!isset($goodsPreferentialPrice[$value['goods_id']])) {
                    $goodsInfo = $e->getTarget()->getServiceLocator()->get('GoodsTable')->infoGoods(array('dbshop_goods.goods_id'=>$value['goods_id'], 'dbshop_goods.goods_state'=>1));
                    //判断优惠价格是否存在，是否过期
                    $preferentialStart = (intval($goodsInfo->goods_preferential_start_time) == 0 or time() >= $goodsInfo->goods_preferential_start_time) ? true : false;
                    $preferentialEnd   = (intval($goodsInfo->goods_preferential_end_time) == 0 or time() <= $goodsInfo->goods_preferential_end_time) ? true : false;
                    $goodsPreferentialPrice[$value['goods_id']] = ($preferentialStart and $preferentialEnd and $goodsInfo->goods_preferential_price > 0) ? $goodsInfo->goods_preferential_price : 0;
                }

                if($goodsPreferentialPrice[$value['goods_id']] <= 0) {
                    if(!empty($value['goods_color']) and !empty($value['goods_size'])) {
                        $userGroupPrice = $e->getTarget()->getServiceLocator()->get('GoodsUsergroupPriceTable')->infoGoodsUsergroupPrice(array('goods_id'=>$value['goods_id'], 'user_group_id'=>$values['group_id'], 'goods_color'=>$value['goods_color'], 'goods_size'=>$value['goods_size']));
                        if(isset($userGroupPrice->goods_user_group_price) and $userGroupPrice->goods_user_group_price > 0 and $userGroupPrice->goods_user_group_price != $value['goods_shop_price']) {
                            $e->getTarget()->getServiceLocator()->get('frontHelper')->editCartSession($key, 'goods_shop_price', $userGroupPrice->goods_user_group_price);
                        }
                    } elseif (!empty($value['goods_adv_tag_id'])) {
                        $userGroupPrice = $e->getTarget()->getServiceLocator()->get('GoodsUsergroupPriceTable')->infoGoodsUsergroupPrice(array('goods_id'=>$value['goods_id'], 'user_group_id'=>$values['group_id'], 'adv_spec_tag_id'=>$value['goods_adv_tag_id']));
                        if(isset($userGroupPrice->goods_user_group_price) and $userGroupPrice->goods_user_group_price > 0 and $userGroupPrice->goods_user_group_price != $value['goods_shop_price']) {
                            $e->getTarget()->getServiceLocator()->get('frontHelper')->editCartSession($key, 'goods_shop_price', $userGroupPrice->goods_user_group_price);
                        }
                    } else {
                        $userGroupPrice = $e->getTarget()->getServiceLocator()->get('GoodsUsergroupPriceTable')->infoGoodsUsergroupPrice(array('goods_id'=>$value['goods_id'], 'user_group_id'=>$values['group_id'], 'goods_color'=>'', 'goods_size'=>'', 'adv_spec_tag_id'=>''));
                        if(isset($userGroupPrice->goods_user_group_price) and $userGroupPrice->goods_user_group_price > 0 and $userGroupPrice->goods_user_group_price != $value['goods_shop_price']) {
                            $e->getTarget()->getServiceLocator()->get('frontHelper')->editCartSession($key, 'goods_shop_price', $userGroupPrice->goods_user_group_price);
                        }
                    }
                }
            }
        }
    }
    /**
     * (api)会员注册和登录的时候，如果之前购物车有信息，则进行会员价格的判断处理（登陆后的购物车商品价格如果存在会员价格，修改为会员价格）
     * @param Event $e
     */
    public function onChangeApiCartUsergroupPirce(Event $e)
    {
        $values      = $e->getParam('values');
        $userUnionid = $e->getTarget()->getServiceLocator()->get('request')->getPost('user_unionid');
        if(!empty($userUnionid)) {
            $cartTable = $e->getTarget()->getServiceLocator()->get('ApiCartTable');
            $cartArray = $cartTable->cartGoods(array('user_unionid'=>$userUnionid));
            if(isset($values['group_id']) and $values['group_id'] > 0 and is_array($cartArray) and !empty($cartArray)) {
                $goodsPreferentialPrice = array();
                foreach($cartArray as $key => $value) {
                    if(!isset($goodsPreferentialPrice[$value['goods_id']])) {
                        $goodsInfo = $e->getTarget()->getServiceLocator()->get('GoodsTable')->infoGoods(array('dbshop_goods.goods_id'=>$value['goods_id'], 'dbshop_goods.goods_state'=>1));
                        //判断优惠价格是否存在，是否过期
                        $preferentialStart = (intval($goodsInfo->goods_preferential_start_time) == 0 or time() >= $goodsInfo->goods_preferential_start_time) ? true : false;
                        $preferentialEnd   = (intval($goodsInfo->goods_preferential_end_time) == 0 or time() <= $goodsInfo->goods_preferential_end_time) ? true : false;
                        $goodsPreferentialPrice[$value['goods_id']] = ($preferentialStart and $preferentialEnd and $goodsInfo->goods_preferential_price > 0) ? $goodsInfo->goods_preferential_price : 0;
                    }

                    if($goodsPreferentialPrice[$value['goods_id']] <= 0) {
                        if(!empty($value['goods_color']) and !empty($value['goods_size'])) {
                            $userGroupPrice = $e->getTarget()->getServiceLocator()->get('GoodsUsergroupPriceTable')->infoGoodsUsergroupPrice(array('goods_id'=>$value['goods_id'], 'user_group_id'=>$values['group_id'], 'goods_color'=>$value['goods_color'], 'goods_size'=>$value['goods_size']));
                            if(isset($userGroupPrice->goods_user_group_price) and $userGroupPrice->goods_user_group_price > 0 and $userGroupPrice->goods_user_group_price != $value['goods_shop_price']) {
                                $cartTable->editCart(array('goods_shop_price'=>$userGroupPrice->goods_user_group_price), array('goods_key'=>$value['goods_key'], 'user_unionid'=>$userUnionid));
                            }
                        } elseif (!empty($value['goods_adv_tag_id'])) {
                            $userGroupPrice = $e->getTarget()->getServiceLocator()->get('GoodsUsergroupPriceTable')->infoGoodsUsergroupPrice(array('goods_id'=>$value['goods_id'], 'user_group_id'=>$values['group_id'], 'adv_spec_tag_id'=>$value['goods_adv_tag_id']));
                            if(isset($userGroupPrice->goods_user_group_price) and $userGroupPrice->goods_user_group_price > 0 and $userGroupPrice->goods_user_group_price != $value['goods_shop_price']) {
                                $cartTable->editCart(array('goods_shop_price'=>$userGroupPrice->goods_user_group_price), array('goods_key'=>$value['goods_key'], 'user_unionid'=>$userUnionid));
                            }
                        } else {
                            $userGroupPrice = $e->getTarget()->getServiceLocator()->get('GoodsUsergroupPriceTable')->infoGoodsUsergroupPrice(array('goods_id'=>$value['goods_id'], 'user_group_id'=>$values['group_id'], 'goods_color'=>'', 'goods_size'=>'', 'adv_spec_tag_id'=>''));
                            if(isset($userGroupPrice->goods_user_group_price) and $userGroupPrice->goods_user_group_price > 0 and $userGroupPrice->goods_user_group_price != $value['goods_shop_price']) {
                                $cartTable->editCart(array('goods_shop_price'=>$userGroupPrice->goods_user_group_price), array('goods_key'=>$value['goods_key'], 'user_unionid'=>$userUnionid));
                            }
                        }
                    }
                }
            }
        }
    }
    /**
     * 缺货通知
     * @param Event $e
     */
    public function goodsStockMessage(Event $e)
    {
        $other = $e->getParam('other');
        if(empty($other) or !is_array($other)) return;

        $orderGoodsTable    = $e->getTarget()->getServiceLocator()->get('OrderGoodsTable');
        $goodsTable         = $e->getTarget()->getServiceLocator()->get('GoodsTable');
        $goodsPriceExtendGoodsTable = $e->getTarget()->getServiceLocator()->get('GoodsPriceExtendGoodsTable');
        $virtualGoodsTable  = $e->getTarget()->getServiceLocator()->get('VirtualGoodsTable');

        $shopName = $e->getTarget()->getServiceLocator()->get('frontHelper')->websiteInfo('shop_name');

        $orderGoods = $orderGoodsTable->listOrderGoods(array('order_id'=>$other['order_id']));
        if(is_array($orderGoods) and !empty($orderGoods)) {
            foreach ($orderGoods as $goodsValue) {
                $goodsInfo = $goodsTable->oneGoodsInfo(array('goods_id'=>$goodsValue['goods_id']));

                //当后台管理开启库存状态显示时，直接跳过该商品检查下一商品
                if($goodsInfo->goods_stock_state_open == 1) continue;

                $stockNum       = $goodsInfo->goods_stock;              //当前商品库存
                $outStockNum    = $goodsInfo->goods_out_of_stock_set;   //当商品缺货时，库存数量

                $whereExtend    = array();
                $goodsStockState= true;
                if($goodsValue['goods_type'] == 1) {//实物商品
                    //判断是否有规格，如果有规格获取扩展表中的商品信息
                    if($goodsInfo->goods_spec_type == 2) {
                        if(isset($goodsValue['goods_spec_tag_id']) and !empty($goodsValue['goods_spec_tag_id'])) {
                            $whereExtend['goods_id']        = $goodsValue['goods_id'];
                            $whereExtend['adv_spec_tag_id'] = $goodsValue['goods_spec_tag_id'];

                            $extendGoods = $goodsPriceExtendGoodsTable->InfoPriceExtendGoods($whereExtend);
                            if(isset($extendGoods->goods_extend_stock) and $extendGoods->goods_extend_stock <= $outStockNum) {
                                $stockNum = $extendGoods->goods_extend_stock;
                                $goodsStockState = false;//说明已经到了库存提醒要求
                            }
                        } else {
                            if($stockNum <= $outStockNum) {
                                $goodsStockState = false;
                            }
                        }
                    } else {
                        if(isset($goodsValue['goods_color']) and !empty($goodsValue['goods_color']) and isset($goodsValue['goods_size']) and !empty($goodsValue['goods_size'])) {
                            $whereExtend['goods_id']    = $goodsValue['goods_id'];
                            $whereExtend['goods_color'] = $goodsValue['goods_color'];
                            $whereExtend['goods_size']  = $goodsValue['goods_size'];

                            $extendGoods = $goodsPriceExtendGoodsTable->InfoPriceExtendGoods($whereExtend);
                            if(isset($extendGoods->goods_extend_stock) and $extendGoods->goods_extend_stock <= $outStockNum) {
                                $stockNum = $extendGoods->goods_extend_stock;
                                $goodsStockState = false;//说明已经到了库存提醒要求
                            }
                        } else {
                            if($stockNum <= $outStockNum) {
                                $goodsStockState = false;
                            }
                        }

                    }
                } else {//虚拟商品
                    $virtualGoodsStockNum = $virtualGoodsTable->countVirtualGoods(array("goods_id=".$goodsValue['goods_id'].' and virtual_goods_state=1 and virtual_goods_account_type=1 and virtual_goods_password_type=1 and virtual_goods_end_time<'.time()));
                    $num = $virtualGoodsStockNum - $goodsValue['buy_num'];
                    if($num <= $outStockNum) {
                        $stockNum = ($num > 0 ? $num : '0');
                        $goodsStockState = false;//说明已经到了库存提醒要求
                    }
                }

                //需要对商品进行缺货提醒
                if(!$goodsStockState) {
                    /*----------------------邮件信息发送----------------------*/
                    $sendMessageBody = $e->getTarget()->getServiceLocator()->get('frontHelper')->getSendMessageBody('goods_stock_out');
                    if($sendMessageBody != '') {
                        $sendArray['subject']       = $shopName . $e->getTarget()->getServiceLocator()->get('translator')->translate('商品缺货提醒');
                        $sendArray['goodsname']     = $goodsValue['goods_name'] . (!empty($goodsValue['goods_extend_info']) ? '(' . strip_tags($goodsValue['goods_extend_info']) . ')' : '');
                        $sendArray['goodsstock']    = (string)$stockNum;
                        $sendArray['send_mail'][]   = $e->getTarget()->getServiceLocator()->get('frontHelper')->getSendMessageAdminEmail('goods_stock_out_state');

                        $sendMessageBody            = $e->getTarget()->getServiceLocator()->get('frontHelper')->createSendMessageContent($sendArray, $sendMessageBody);
                        try {
                            $e->getTarget()->getServiceLocator()->get('shop_send_mail')->SendMesssageMail($sendArray, $sendMessageBody);
                        } catch (\Exception $e) {

                        }
                    }
                    /*----------------------邮件信息发送----------------------*/

                    /*----------------------手机短信信息发送----------------------*/
                    $smsData = array(
                        'shopname'      => $shopName,
                        'goodsname'     => $goodsValue['goods_name'] . (!empty($goodsValue['goods_extend_info']) ? '(' . strip_tags($goodsValue['goods_extend_info']) . ')' : ''),
                        'goodsstock'    => (string)$stockNum
                    );
                    try {
                        $e->getTarget()->getServiceLocator()->get('shop_send_sms')->toSendSms(
                            $smsData,
                            '',
                            'alidayu_goods_stock_template_id',
                            rand(1, 100)
                        );
                    } catch(\Exception $e) {

                    }
                    /*----------------------手机短信信息发送----------------------*/
                }
            }
        }
    }
    public function onSendVirtualGoods(Event $e)
    {
        $orderId = $e->getParam('values');

        $orderGoodsTable    = $e->getTarget()->getServiceLocator()->get('OrderGoodsTable');
        $virtualGoodsTable  = $e->getTarget()->getServiceLocator()->get('VirtualGoodsTable');
        $goodsTable         = $e->getTarget()->getServiceLocator()->get('GoodsTable');
        $userTable          = $e->getTarget()->getServiceLocator()->get('UserTable');
        $orderTable         = $e->getTarget()->getServiceLocator()->get('OrderTable');

        //虚拟商品处理
        $orderInfo  = $orderTable->infoOrder(array('order_id'=>$orderId));
        //查看是否有虚拟商品，如果有进行虚拟商品处理
        $virtualGoodsArray = $orderGoodsTable->listOrderGoods(array('order_id'=>$orderId, 'buyer_id'=>$orderInfo->buyer_id, 'goods_type'=>2));
        if(is_array($virtualGoodsArray) and !empty($virtualGoodsArray)) {
            $virtualGoodsStockFew = array();//虚拟商品库存不足时，存储内容的数组
            foreach($virtualGoodsArray as $orderVirtualGoods) {
                if(isset($orderVirtualGoods['buy_num']) and $orderVirtualGoods['buy_num'] > 0) {
                    for($i=0; $i<$orderVirtualGoods['buy_num']; $i++) {
                        $virtualGoodsInfo = $virtualGoodsTable->infoVirtualGoods(array('goods_id'=>$orderVirtualGoods['goods_id'], 'virtual_goods_state'=>1));
                        if(isset($virtualGoodsInfo[0]) and is_array($virtualGoodsInfo[0]) and !empty($virtualGoodsInfo[0])) {
                            $updateVirtualGoods = array();
                            $updateVirtualGoods['order_sn'] = $orderInfo->order_sn;
                            $updateVirtualGoods['virtual_goods_state'] = 2;
                            $updateVirtualGoods['order_id'] = $orderInfo->order_id;
                            $updateVirtualGoods['user_id']  = $orderInfo->buyer_id;
                            $updateVirtualGoods['user_name']= $orderInfo->buyer_name;

                            if($virtualGoodsInfo[0]['virtual_goods_account_type'] != 1 or $virtualGoodsInfo[0]['virtual_goods_password_type'] != 1) {
                                mt_srand((double) microtime() * 1000000);
                                if($virtualGoodsInfo[0]['virtual_goods_account_type'] == 2) $updateVirtualGoods['virtual_goods_account'] = 'U' . date('Ymd') . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
                                if(in_array($virtualGoodsInfo[0]['virtual_goods_account_type'], array(1,3))) $updateVirtualGoods['virtual_goods_account'] = $virtualGoodsInfo[0]['virtual_goods_account'];

                                $chars = array("a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z");
                                if($virtualGoodsInfo[0]['virtual_goods_password_type'] == 2) $updateVirtualGoods['virtual_goods_password'] = $chars[rand(0, 25)] . $chars[rand(0, 25)] . str_replace('.', '',microtime(true));;
                                if(in_array($virtualGoodsInfo[0]['virtual_goods_password_type'], array(1,3))) $updateVirtualGoods['virtual_goods_password'] = $virtualGoodsInfo[0]['virtual_goods_password'];

                                $updateVirtualGoods['virtual_goods_account_type'] = $virtualGoodsInfo[0]['virtual_goods_account_type'];
                                $updateVirtualGoods['virtual_goods_password_type'] = $virtualGoodsInfo[0]['virtual_goods_password_type'];
                                $updateVirtualGoods['goods_id'] = $virtualGoodsInfo[0]['goods_id'];
                                if($virtualGoodsInfo[0]['virtual_goods_end_time'] != 0) $updateVirtualGoods['virtual_goods_end_time'] = $virtualGoodsInfo[0]['virtual_goods_end_time'];

                                $virtualGoodsTable->addVirtualGoods($updateVirtualGoods);
                            } else {
                                if($i == 0) {
                                    $virtualGoodsNum = $virtualGoodsTable->countVirtualGoods(array('goods_id'=>$orderVirtualGoods['goods_id'], 'virtual_goods_state'=>1));
                                    if($virtualGoodsNum < $orderVirtualGoods['buy_num']) {//当第一次执行，发现虚拟可用的虚拟商品数量，不足以发货时，跳出处理
                                        $virtualGoodsStockFew[] = $orderVirtualGoods['goods_id'];
                                        break;//跳出for循环，继续执行foreach循环
                                    }
                                }

                                $virtualGoodsTable->updateVirtualGoods($updateVirtualGoods, array('virtual_goods_id'=>$virtualGoodsInfo[0]['virtual_goods_id']));
                            }
                        } else {
                            $virtualGoodsStockFew[] = $orderVirtualGoods['goods_id'];
                            break;//跳出for循环，继续执行foreach循环
                        }
                    }
                }
            }
            //如果订单中没有实物商品，则发货处理
            $vGoodsInfo = $orderGoodsTable->InfoOrderGoods(array('order_id'=>$orderId, 'buyer_id'=>$orderInfo->buyer_id, 'goods_type'=>1));
            if(empty($vGoodsInfo)) {
                //当$virtualGoodsStockFew为空时，说明虚拟商品库存充足，可以自动发货；当部位空时，说明有商品库存不足只进行了部分自动发货，因此设置为发货中
                if(empty($virtualGoodsStockFew)) $orderTable->updateOrder(array('order_state'=>40, 'express_time'=>time()), array('order_id'=>$orderId));
                else $orderTable->updateOrder(array('order_state'=>30, 'express_time'=>time()), array('order_id'=>$orderId));
            }
        }

        $shopName = $e->getTarget()->getServiceLocator()->get('frontHelper')->websiteInfo('shop_name');

        //获取订单商品中的虚拟商品，切已经售出，并且购买数量为1的商品
        //$orderGoodsArray = $orderGoodsTable->listOrderGoods(array('order_id'=>$orderId, 'goods_type'=>2, 'buy_num'=>1));
        $orderGoodsArray = $orderGoodsTable->listOrderGoods(array('order_id'=>$orderId, 'goods_type'=>2));
        $emailVirtualGoodsStr   = '';
        $emailVirtualGoodsName  = '';
        $smsSendArray = array();
        if(!empty($orderGoodsArray)) {
            $userInfo = $userTable->infoUser(array('user_id'=>$orderGoodsArray[0]['buyer_id']));
            foreach ($orderGoodsArray as $goodsValue) {
                $virtualGoodsInfo   = $virtualGoodsTable->infoVirtualGoods(array('order_id'=>$orderId, 'user_id'=>$goodsValue['buyer_id'], 'virtual_goods_state'=>2, 'goods_id'=>$goodsValue['goods_id']));
                $goodsInfo          = $goodsTable->oneGoodsInfo(array('goods_id'=>$goodsValue['goods_id']));
                if(!empty($virtualGoodsInfo)) {
                    $emailVirtualNum= 1;
                    if(isset($goodsInfo->virtual_email_send) and $goodsInfo->virtual_email_send == 1) {//电邮发送
                        $emailVirtualGoodsName   .= $goodsValue['goods_name'].',';
                        $emailVirtualGoodsStr    .= $goodsValue['goods_name'];
                    }

                    foreach ($virtualGoodsInfo as $vGoodsKey => $vGoodsValue) {
                        if(isset($goodsInfo->virtual_email_send) and $goodsInfo->virtual_email_send == 1) {//电邮发送
                            $emailVirtualGoodsStr    .= $e->getTarget()->getServiceLocator()->get('translator')->translate('账号').$emailVirtualNum.':'.$vGoodsValue['virtual_goods_account'] . '&nbsp;&nbsp;';
                            $emailVirtualGoodsStr    .= $e->getTarget()->getServiceLocator()->get('translator')->translate('密码').$emailVirtualNum.':'.$vGoodsValue['virtual_goods_password'].'<br>';

                            $emailVirtualNum++;
                        }
                        if(isset($goodsInfo->virtual_phone_send) and $goodsInfo->virtual_phone_send == 1) {//短信发送
                            $smsSendArray[] = array(
                                'shopname' => $shopName,
                                'goodsname' => $goodsValue['goods_name'],
                                'virtualaccount' => $vGoodsValue['virtual_goods_account'],
                                'virtualpassword'=> $vGoodsValue['virtual_goods_password']
                            );
                        }
                    }
                }
            }

            //电邮发送
            if(!empty($emailVirtualGoodsName)) {
                $sendMessageBody = $e->getTarget()->getServiceLocator()->get('frontHelper')->getSendMessageBody('virtual_goods_send');
                if(!empty($sendMessageBody)) {
                    $sendArray = array();
                    $sendArray['subject']       = $shopName . $e->getTarget()->getServiceLocator()->get('translator')->translate('虚拟商品信息');
                    $sendArray['shopname']      = $shopName;
                    $sendArray['goodsname']     = $emailVirtualGoodsName;
                    $sendArray['virtualaccount']= $emailVirtualGoodsStr;
                    $sendArray['virtualpassword']= '以上为账号信息。';
                    $sendArray['send_mail'][]   = $e->getTarget()->getServiceLocator()->get('frontHelper')->getSendMessageBuyerEmail('virtual_goods_send_state', $userInfo->user_email);
                    $sendArray['send_mail'][]   = $e->getTarget()->getServiceLocator()->get('frontHelper')->getSendMessageAdminEmail('virtual_goods_send_state');

                    $sendMessageBody            = $e->getTarget()->getServiceLocator()->get('frontHelper')->createSendMessageContent($sendArray, $sendMessageBody);
                    try {
                        $e->getTarget()->getServiceLocator()->get('shop_send_mail')->SendMesssageMail($sendArray, $sendMessageBody);
                    } catch (\Exception $e) {

                    }
                }
            }

            //短信发送
            if(!empty($smsSendArray)) {
                try {
                    $e->getTarget()->getServiceLocator()->get('shop_send_sms')->toSendSms(
                        array('sendMore' => $smsSendArray),
                        $userInfo->user_phone,
                        'alidayu_virtual_goods_send_template_id',
                        rand(1, 100)
                    );
                } catch(\Exception $e) {

                }
            }

        }
    }

}