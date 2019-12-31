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

namespace User\Event;

use User\Service\IntegralRuleService;
use Zend\Config\Reader\Ini;
use Zend\EventManager\Event;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Session\Container;

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
        $this->listeners[] = $shareEvents->attach('Orders\Controller\OrdersController', 'order.changeAmount.backstage.post',
            array($this, 'onChangeUserOrderIntegral')
        );
        $this->listeners[] = $shareEvents->attach('User\Controller\UserController', 'user.info.backstage.post',
            array($this, 'onShowUserRegExtend')
        );
        $this->listeners[] = $shareEvents->attach('User\Controller\UserController', 'user.save.backstage.post',
            array($this, 'onSaveUserRegExtend')
        );
        $this->listeners[] = $shareEvents->attach('User\Controller\UserController', 'user.del.backstage.post',
            array($this, 'onDelUserRegExtend')
        );
        $this->listeners[] = $shareEvents->attach('User\Controller\UserController', 'user.alldel.backstage.post',
            array($this, 'onAllDelUserRegExtend')
        );
        $this->listeners[] = $shareEvents->attach('User\Controller\UsergroupController', 'userGroup.del.backstage.post',
            array($this, 'onDelUserGroupPrice')
        );
        //删除会员的时候，同时删除会员内的优惠券
        $this->listeners[] = $shareEvents->attach('User\Controller\UserController', 'user.del.backstage.post',
            array($this, 'onDelUserCoupon')
        );
        $this->listeners[] = $shareEvents->attach('User\Controller\UserController', 'user.alldel.backstage.post',
            array($this, 'onAllDelUserCoupon')
        );
        //删除会员的时候，同时删除会员购物车内的商品
        $this->listeners[] = $shareEvents->attach('User\Controller\UserController', 'user.del.backstage.post',
            array($this, 'onDelUserCart')
        );
        $this->listeners[] = $shareEvents->attach('User\Controller\UserController', 'user.alldel.backstage.post',
            array($this, 'onAllDelUserCart')
        );
        //后台的两个完成状态操作，都可以将订单进行完成处理
        $this->listeners[] = $shareEvents->attach('Orders\Controller\OrdersController', 'order.finish.backstage.post',
            array($this, 'onUpdateUserCoupon')
        );
        //PC前台
        $this->listeners[] = $shareEvents->attach(
            array(
                'Shopfront\Controller\UserController',
                'Mobile\Controller\UserController'
            ),
            'user.regshow.front.post',
            array($this, 'onShowUserRegExtend')
        );
        $this->listeners[] = $shareEvents->attach(
            array(
                'Shopfront\Controller\UserController',
                'Mobile\Controller\UserController',
                'Dbapi\Controller\JsonapiController'
            ), 'user.register.front.pre',
            array($this, 'onSaveUserRegExtend')
        );
        $this->listeners[] = $shareEvents->attach(
            array(
                'Shopfront\Controller\HomeController',
                'Mobile\Controller\HomeController'
            ),
            'user.edit.front.post',
            array($this, 'onShowUserRegExtend')
        );
        $this->listeners[] = $shareEvents->attach(
            array(
                'Shopfront\Controller\HomeController',
                'Mobile\Controller\HomeController',
                //'Dbapi\Controller\JsonapiController'
            ), 'user.edit.front.pre',
            array($this, 'onSaveUserRegExtend')
        );
        $this->listeners[] = $shareEvents->attach(
            array(
                'Shopfront\Controller\OrderController',
                'Mobile\Controller\OrderController',
                'Dbapi\Controller\JsonapiController'
            ), 'order.finish.front.post',
            array($this, 'onUpdateUserCoupon')
        );

        $this->listeners[] = $shareEvents->attach(
            array(
                'Shopfront\Controller\CartController',
                'Mobile\Controller\CartController',
                'Dbapi\Controller\JsonapiController'
            ),
            'cart.submit.front.post',
            array($this, 'buyGetUserCoupon')
        );

        $this->listeners[] = $shareEvents->attach(
            array(
                'Shopfront\Controller\UserController',
                'Mobile\Controller\UserController',
                'Dbapi\Controller\JsonapiController'
            ), 'user.register.front.pre',
            array($this, 'registerGetUserCoupon')
        );

        //购物车添加动作
        $this->listeners[] = $shareEvents->attach(
            array(
                'Shopfront\Controller\CartController'
            ), 'cart.add.front.post',
            array($this, 'cartTableAdd')
        );
        //购物车编辑动作
        $this->listeners[] = $shareEvents->attach(
            array(
                'Shopfront\Controller\CartController'
            ), 'cart.edit.front.post',
            array($this, 'cartTableUpdate')
        );
        //购物车清空动作
        $this->listeners[] = $shareEvents->attach(
            array(
                'Shopfront\Controller\CartController'
            ), 'cart.clear.front.post',
            array($this, 'cartTableClear')
        );
        //订单提交后，清空购物车数据表
        $this->listeners[] = $shareEvents->attach(
            array(
                'Shopfront\Controller\CartController',
                'Mobile\Controller\CartController'
            ),
            'cart.submit.front.post',
            array($this, 'cartTableClear')
        );
        //购物车删除动作
        $this->listeners[] = $shareEvents->attach(
            array(
                'Shopfront\Controller\CartController'
            ), 'cart.del.front.post',
            array($this, 'cartTableDel')
        );
        //登录完成，对session中的购物车信息进行处理
        $this->listeners[] = $shareEvents->attach(
            array(
                'Shopfront\Controller\UserController',
                'Mobile\Controller\UserController'
            ),
            'user.login.front.post',
            array($this, 'cartTableOper')
        );
        //注册完成，对session中的购物车信息进行处理
        $this->listeners[] = $shareEvents->attach(
            array(
                'Shopfront\Controller\UserController',
                'Mobile\Controller\UserController'
            ),
            'user.register.front.post',
            array($this, 'cartTableOper')
        );
        //订单提交前，对时间点进行判断是否自动刷单
        $this->listeners[] = $shareEvents->attach(
            array(
                'Shopfront\Controller\CartController',
                'Mobile\Controller\CartController'
            ),
            'cart.submit.front.pre',
            array($this, 'checkSubmitTimeSession')
        );
        //订单提交后，设置提交时间点，用来判断用户是否自动刷单
        $this->listeners[] = $shareEvents->attach(
            array(
                'Shopfront\Controller\CartController',
                'Mobile\Controller\CartController'
            ),
            'cart.submit.front.post',
            array($this, 'submitTimeSession')
        );
        //phone前台

        //手机api接口
        $this->listeners[] = $shareEvents->attach('Dbapi\Controller\JsonapiController', 'user.info.front.post',
            array($this, 'onShowUserRegExtendInfo')
        );

        //添加购物车对于是否登录显示的判断
        $this->listeners[] = $shareEvents->attach('Shopfront\Controller\CartController',
            'cart.add.front.pre',
            array($this, 'onCheckLoginShowPrice')
        );
        $this->listeners[] = $shareEvents->attach('Dbapi\Controller\JsonapiController',
            'cart.add.front.pre',
            array($this, 'onApiCheckLoginShowPrice')
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
     * 后台订单修改价格，如果有积分，对积分进行重新计算(只对全品类商品类型进行计算，不区分商品分类、商品品牌等)
     * @param Event $e
     */
    public function onChangeUserOrderIntegral(Event $e)
    {
        $values = $e->getParam('values');

        $orderTable = $e->getTarget()->getServiceLocator()->get('OrderTable');

        $orderInfo  = $orderTable->infoOrder(array('order_id'=>$values['order_id']));

        if($orderInfo->integral_num > 0 || $orderInfo->integral_type_2_num > 0) {
            $integralRuleService = new IntegralRuleService();
            $updateArray = array();
            if($orderInfo->integral_num > 0) {
                $integralInfo = $integralRuleService->changeOrderAmountIntegral($values['order_edit_amount']);
                if(isset($integralInfo['integralNum'])) {
                    $updateArray['integral_num']        = $integralInfo['integralNum'];
                    $updateArray['integral_rule_info']  = $integralInfo['integralRuleInfo'];
                } else {
                    $updateArray['integral_num']        = 0;
                    $updateArray['integral_rule_info']  = null;
                }
            }
            if($orderInfo->integral_type_2_num > 0) {
                $integralInfo2 = $integralRuleService->changeOrderAmountIntegral($values['order_edit_amount'], 2);
                if(isset($integralInfo2['integralNum'])) {
                    $updateArray['integral_type_2_num']             = $integralInfo2['integralNum'];
                    $updateArray['integral_type_2_num_rule_info']   = $integralInfo2['integralRuleInfo'];
                } else {
                    $updateArray['integral_type_2_num']             = 0;
                    $updateArray['integral_type_2_num_rule_info']   = null;
                }
            }

            if(!empty($updateArray))  $orderTable->updateOrder($updateArray, array('order_id'=>$values['order_id']));
        }
    }

    /**
     * 前台（包括手机端），提交订单前，判断该订单用户是否刷单
     * @param Event $e
     */
    public function checkSubmitTimeSession(Event $e)
    {
        $submitOrderSession = new Container('submit_order');
        if(isset($submitOrderSession->expiryTime) and (time() - $submitOrderSession->lastSubmitTime)<=$submitOrderSession->expiryTime) {
            $e->getTarget()->getServiceLocator()->get('frontHelper')->clearCartSession();
            exit('请在'.round(($submitOrderSession->expiryTime - (time() - $submitOrderSession->lastSubmitTime))/60).'分钟后，在进行下单！');
        }
        if(isset($submitOrderSession->lastSubmitTime) and (time() - $submitOrderSession->lastSubmitTime)<=6) {
            $submitOrderSession->expiryTime = 1800;
            $e->getTarget()->getServiceLocator()->get('frontHelper')->clearCartSession();
            exit('恶意下单！');
        }
    }
    /**
     * 前台（包括手机端），提交订单后，设置提交时间
     * @param Event $e
     */
    public function submitTimeSession(Event $e)
    {
        $submitOrderSession = new Container('submit_order');
        $submitOrderSession->lastSubmitTime = time();
    }
    /**
     * 会员组删除后，同时删除会员组价格
     * @param Event $e
     */
    public function onDelUserGroupPrice(Event $e)
    {
        $values = $e->getParam('values');
        $userGroupId = $values['group_id'];
        if($userGroupId > 0) {
            $e->getTarget()->getServiceLocator()->get('GoodsUsergroupPriceTable')->delGoodsUsergroupPrice(array('user_group_id'=>$userGroupId));
        }
    }
    /**
     * 后台批量删除用户，对应用户的购物车商品进行删除
     * @param Event $e
     */
    public function onAllDelUserCart(Event $e)
    {
        $values = $e->getParam('values');
        if(is_array($values) and !empty($values)) {
            $e->getTarget()->getServiceLocator()->get('CartTable')->delCart(array('user_id IN ('.implode(',', $values).')'));
        }
    }
    /**
     * 后台单独删除一个用户，则该用户对应的购物车商品进行删除
     * @param Event $e
     */
    public function onDelUserCart(Event $e)
    {
        $values = $e->getParam('values');
        if(isset($values->user_id) and $values->user_id > 0) {
            $e->getTarget()->getServiceLocator()->get('CartTable')->delCart(array('user_id'=>$values->user_id));
        }
    }
    /**
     * 当会员添加购物车时，将购物车信息加入数据表
     * @param Event $e
     */
    public function cartTableAdd(Event $e)
    {
        $array = $e->getParam('values');
        $userId= $e->getTarget()->getServiceLocator()->get('frontHelper')->getUserSession('user_id');
        if($userId > 0) {
            $cartData = $array['cart'];
            $cartData['goods_key']  = $array['key'];
            $cartData['user_id']    = $userId;

            $e->getTarget()->getServiceLocator()->get('CartTable')->addCart($cartData);
        }
    }
    /**
     * 当会员添加购物车时，将购物车信息在数据表中更新
     * @param Event $e
     */
    public function cartTableUpdate(Event $e)
    {
        $array = $e->getParam('values');
        $userId= $e->getTarget()->getServiceLocator()->get('frontHelper')->getUserSession('user_id');
        if($userId > 0) {
            if($array['editType'] == 'buy_num') {
                $e->getTarget()->getServiceLocator()->get('CartTable')->editCart(array('buy_num'=>$array['value']), array('goods_key'=>$array['key'], 'user_id'=>$userId));
            }
        }
    }
    /**
     * 当会员添加购物车时，将购物车信息清空数据表
     * @param Event $e
     */
    public function cartTableClear(Event $e)
    {
        $userId= $e->getTarget()->getServiceLocator()->get('frontHelper')->getUserSession('user_id');
        if($userId > 0) {
            $e->getTarget()->getServiceLocator()->get('CartTable')->delCart(array('user_id'=>$userId));
        }
    }
    /**
     * 当会员添加购物车时，将购物车商品信息从数据表中删除
     * @param Event $e
     */
    public function cartTableDel(Event $e)
    {
        $goodsKey = $e->getParam('key');
        $userId= $e->getTarget()->getServiceLocator()->get('frontHelper')->getUserSession('user_id');
        if($userId > 0) {
            $e->getTarget()->getServiceLocator()->get('CartTable')->delCart(array('user_id'=>$userId, 'goods_key'=>$goodsKey));
        }
    }
    /**
     * 会员登录或者注册完成后，对购物车的操作处理
     * @param Event $e
     */
    public function cartTableOper(Event $e)
    {
        $userId         = $e->getTarget()->getServiceLocator()->get('frontHelper')->getUserSession('user_id');
        $userGroupId    = $e->getTarget()->getServiceLocator()->get('frontHelper')->getUserSession('group_id');
        $sessionCart    = $e->getTarget()->getServiceLocator()->get('frontHelper')->getCartSession();
        if($userId > 0) {
            $cartTable  = $e->getTarget()->getServiceLocator()->get('CartTable');
            $goodsTable = $e->getTarget()->getServiceLocator()->get('GoodsTable');
            $GoodsPriceExtendGoodsTable = $e->getTarget()->getServiceLocator()->get('GoodsPriceExtendGoodsTable');
            $GoodsUsergroupPriceTable   = $e->getTarget()->getServiceLocator()->get('GoodsUsergroupPriceTable');
            $GoodsInClassTable          = $e->getTarget()->getServiceLocator()->get('GoodsInClassTable');

            $whereKeyStr = '';
            if(!empty($sessionCart)) {
                foreach ($sessionCart as $key => $val) {
                    $cartGoods = $cartTable->infoCart(array('goods_key'=>$key, 'user_id'=>$userId));
                    if(!empty($cartGoods)) {
                        $cartTable->editCart($val, array('goods_key'=>$key, 'user_id'=>$userId));
                    } else {
                        $val['user_id']     = $userId;
                        $val['goods_key']   = $key;
                        $cartTable->addCart($val);
                    }
                    $whereKeyStr .= "goods_key!='".$key."' and ";
                }
            }

            if(!empty($whereKeyStr)) {
                $whereKeyStr .= 'user_id='.$userId;
                $goodsArray = $cartTable->cartGoods(array($whereKeyStr));
            } else {
                $goodsArray = $cartTable->cartGoods(array('user_id'=>$userId));
            }
            if(is_array($goodsArray) and !empty($goodsArray)) {
                foreach ($goodsArray as $goodsValue) {
                    //判断商品是否依然存在
                    $goodsInfo = $goodsTable->infoGoods(array('dbshop_goods.goods_id'=>$goodsValue['goods_id'], 'dbshop_goods.goods_state'=>1));
                    if(empty($goodsInfo)) {
                        $cartTable->delCart(array('goods_id'=>$goodsValue['goods_id'], 'user_id'=>$userId));
                        continue;
                    }

                    //判断优惠价格是否存在，是否过期
                    $preferentialStart = (intval($goodsInfo->goods_preferential_start_time) == 0 or time() >= $goodsInfo->goods_preferential_start_time) ? true : false;
                    $preferentialEnd   = (intval($goodsInfo->goods_preferential_end_time) == 0 or time() <= $goodsInfo->goods_preferential_end_time) ? true : false;
                    $goodsInfo->goods_preferential_price = ($preferentialStart and $preferentialEnd and $goodsInfo->goods_preferential_price > 0) ? $goodsInfo->goods_preferential_price : 0;

                    $extendGoods = array();
                    if($goodsInfo->goods_spec_type == 2) {//规格高级模式
                        if(!empty($goodsValue['goods_adv_tag_id'])) {
                            $extendGoods = $GoodsPriceExtendGoodsTable->InfoPriceExtendGoods(array('adv_spec_tag_id'=>$goodsValue['goods_adv_tag_id'], 'goods_id'=>$goodsValue['goods_id']));
                            if(empty($extendGoods)) {
                                $cartTable->delCart(array('goods_key'=>$goodsValue['goods_key'], 'user_id'=>$userId));
                                continue;
                            }
                            $goodsInfo->goods_stock      = $extendGoods->goods_extend_stock;
                            $goodsInfo->goods_item       = $extendGoods->goods_extend_item;
                            $goodsInfo->goods_shop_price = ($goodsInfo->goods_preferential_price <= 0 ? $extendGoods->goods_extend_price : $goodsInfo->goods_preferential_price);

                            //当未开启优惠价，判断是否有会员价
                            if($goodsInfo->goods_preferential_price <= 0) {
                                $userGroupPrice = $GoodsUsergroupPriceTable->infoGoodsUsergroupPrice(array('goods_id'=>$goodsInfo->goods_id, 'user_group_id'=>$userGroupId, 'adv_spec_tag_id'=>$goodsValue['goods_adv_tag_id']));
                                if(isset($userGroupPrice->goods_user_group_price) and $userGroupPrice->goods_user_group_price > 0) $goodsInfo->goods_shop_price = $userGroupPrice->goods_user_group_price;
                            }
                        } else {
                            //当未开启优惠价，判断是否有会员价
                            if($goodsInfo->goods_preferential_price <= 0) {
                                $userGroupPrice = $GoodsUsergroupPriceTable->infoGoodsUsergroupPrice(array('goods_id'=>$goodsInfo->goods_id, 'user_group_id'=>$userGroupId, 'goods_color'=>'', 'goods_size'=>'', 'adv_spec_tag_id'=>''));
                                if(isset($userGroupPrice->goods_user_group_price) and $userGroupPrice->goods_user_group_price > 0) $goodsInfo->goods_shop_price = $userGroupPrice->goods_user_group_price;
                            }
                        }
                    } else {
                        if(!empty($goodsValue['goods_color']) and !empty($goodsValue['goods_size'])) {
                            $extendGoods = $GoodsPriceExtendGoodsTable->InfoPriceExtendGoods(array('goods_color'=>$goodsValue['goods_color'], 'goods_size'=>$goodsValue['goods_size'], 'goods_id'=>$goodsValue['goods_id']));
                            if(empty($extendGoods)) {
                                $cartTable->delCart(array('goods_key'=>$goodsValue['goods_key'], 'user_id'=>$userId));
                                continue;
                            }
                            $goodsInfo->goods_stock      = $extendGoods->goods_extend_stock;
                            $goodsInfo->goods_item       = $extendGoods->goods_extend_item;
                            $goodsInfo->goods_shop_price = ($goodsInfo->goods_preferential_price <= 0 ? $extendGoods->goods_extend_price : $goodsInfo->goods_preferential_price);

                            //当未开启优惠价，判断是否有会员价
                            if($goodsInfo->goods_preferential_price <= 0) {
                                $userGroupPrice = $GoodsUsergroupPriceTable->infoGoodsUsergroupPrice(array('goods_id'=>$goodsInfo->goods_id, 'user_group_id'=>$userGroupId, 'goods_color'=>$goodsValue['goods_color'], 'goods_size'=>$goodsValue['goods_size']));
                                if(isset($userGroupPrice->goods_user_group_price) and $userGroupPrice->goods_user_group_price > 0) $goodsInfo->goods_shop_price = $userGroupPrice->goods_user_group_price;
                            }
                        } else {
                            //判断是否有优惠价格存在
                            $goodsInfo->goods_shop_price = ($goodsInfo->goods_preferential_price <= 0 ? $goodsInfo->goods_shop_price : $goodsInfo->goods_preferential_price);
                            //当未开启优惠价，判断是否有会员价
                            if($goodsInfo->goods_preferential_price <= 0 ) {
                                $userGroupPrice = $GoodsUsergroupPriceTable->infoGoodsUsergroupPrice(array('goods_id'=>$goodsInfo->goods_id, 'user_group_id'=>$userGroupId, 'goods_color'=>'', 'goods_size'=>'', 'adv_spec_tag_id'=>''));
                                if(isset($userGroupPrice->goods_user_group_price) and $userGroupPrice->goods_user_group_price > 0) $goodsInfo->goods_shop_price = $userGroupPrice->goods_user_group_price;
                            }
                        }
                    }

                    //判断购物数是否超过库存数
                    if($goodsValue['buy_num'] > $goodsInfo->goods_stock and $goodsInfo->goods_stock_state_open != 1) {
                        $goodsValue['buy_num'] = $goodsInfo->goods_stock;
                    }
                    //判断是否有最少购买数量限制
                    if($goodsInfo->goods_cart_buy_min_num > 0 and $goodsInfo->goods_cart_buy_min_num > $goodsValue['buy_num']) {
                        $goodsValue['buy_num'] = $goodsInfo->goods_cart_buy_min_num;
                    }
                    //判断是否有最多购买数量限制
                    if($goodsInfo->goods_cart_buy_max_num > 0 and $goodsInfo->goods_cart_buy_max_num < $goodsValue['buy_num']) {
                        $goodsValue['buy_num'] = $goodsInfo->goods_cart_buy_max_num;
                    }
                    //判断在没有数量限制的情况下，是否超过了购买数量
                    if($goodsInfo->goods_cart_buy_max_num == 0 and $goodsValue['buy_num'] > 50 and $goodsInfo->goods_cart_buy_min_num == 0) {
                        $goodsValue['buy_num'] = 50;
                    }

                    //获取商品的N个分类信息，主要用于优惠规则中
                    $classIdArray = $GoodsInClassTable->listGoodsInClass(array('goods_id'=>$goodsInfo->goods_id));

                    $goodsValue['goods_name']           = $goodsInfo->goods_name;
                    $goodsValue['class_id']             = (isset($goodsInfo->main_class_id) and $goodsInfo->main_class_id > 0) ?  $goodsInfo->main_class_id : $goodsValue['class_id'];
                    $goodsValue['goods_image']          = $goodsInfo->goods_thumbnail_image;
                    $goodsValue['goods_stock_state']    = $goodsInfo->goods_stock_state_open;
                    $goodsValue['goods_stock']          = $goodsInfo->goods_stock;
                    $goodsValue['goods_shop_price']     = $goodsInfo->goods_shop_price;
                    $goodsValue['goods_item']           = (isset($goodsInfo->goods_item)      ? $goodsInfo->goods_item  : '');
                    $goodsValue['goods_weight']         = (isset($goodsInfo->goods_weight)    ? $goodsInfo->goods_weight : 0);
                    $goodsValue['buy_min_num']          = $goodsInfo->goods_cart_buy_min_num;
                    $goodsValue['buy_max_num']          = $goodsInfo->goods_cart_buy_max_num;
                    $goodsValue['integral_num']         = isset($goodsInfo->goods_integral_num) ? ($goodsInfo->goods_integral_num > 0 ? $goodsInfo->goods_integral_num : 0) : 0;
                    $goodsValue['brand_id']             = $goodsInfo->brand_id;
                    $goodsValue['class_id_array']       = $classIdArray;

                    $goodsKey = $goodsValue['goods_key'];
                    unset($goodsValue['goods_key']);
                    $e->getTarget()->getServiceLocator()->get('frontHelper')->setCartSession($goodsKey, $goodsValue);
                }
            }
        }
    }
    /**
     * 检查是否登录后可见价格，如果是，则必须登录后才可以购买商品
     */
    public function onCheckLoginShowPrice(Event $e)
    {
        $showState = $e->getTarget()->getServiceLocator()->get('frontHelper')->getDbshopGoodsIni('dbshop_login_goods_price_show');
        $session   = new Container('user_info');
        if($showState == 1 and !isset($session->user_id)) return array('state'=>false, 'message'=>$e->getTarget()->getServiceLocator()->get('translator')->translate('登录后，才能购买商品！Login'));
    }

    /**
     * 检查是否登录后可见价格，如果是，则必须登录后才可以购买商品（Api）
     */
    public function onApiCheckLoginShowPrice(Event $e)
    {
        $array = $e->getParam('values');

        $showState = $e->getTarget()->getServiceLocator()->get('frontHelper')->getDbshopGoodsIni('dbshop_login_goods_price_show');
        if($showState == 1 and !isset($array['user']['user_id'])) return array('state'=>false, 'message'=>$e->getTarget()->getServiceLocator()->get('translator')->translate('登录后，才能购买商品！'));
    }
    /**
     * 显示会员扩展信息
     * @param Event $e
     * @return array
     */
    public function onShowUserRegExtend(Event $e)
    {
        $values     = $e->getParam('values');
        $fieldValue = array();
        if(isset($values['user_info']->user_id) and $values['user_info']->user_id > 0) {
            $userRegExtendTable = $e->getTarget()->getServiceLocator()->get('UserRegExtendTable');
            $fieldValue = $userRegExtendTable->infoUserRegExtend(array('user_id'=>$values['user_info']->user_id));
        }

        $httpHost = $e->getTarget()->getServiceLocator()->get('frontHelper')->dbshopHttpHost();
        $httpType = $e->getTarget()->getServiceLocator()->get('frontHelper')->dbshopHttpOrHttps();
        $baseUrl  = $e->getTarget()->getServiceLocator()->get('Request')->getbaseUrl();

        $userRegExtendFieldTable = $e->getTarget()->getServiceLocator()->get('UserRegExtendFieldTable');
        $fieldArray = $userRegExtendFieldTable->listUserRegExtendField(array('field_state'=>1));

        $showField  = $this->createUserRegExtendField($fieldArray, array('http_host'=>$httpHost, 'http_type'=>$httpType, 'base_url'=>$baseUrl), $fieldValue);

        return array('user_reg_extend'=>$showField);
    }
    /**
     * 输出会员扩展信息内容，用于前台获取会员信息时一起显示出来
     * @param Event $e
     * @return mixed
     */
    public function onShowUserRegExtendInfo(Event $e)
    {
        $values = $e->getParam('values');
        if($values['user_id'] > 0) {
            $userRegExtendTable = $e->getTarget()->getServiceLocator()->get('UserRegExtendTable');
            $fieldValue = $userRegExtendTable->infoUserRegExtend(array('user_id'=>$values['user_id']));

            $httpHost = $e->getTarget()->getServiceLocator()->get('frontHelper')->dbshopHttpHost();
            $httpType = $e->getTarget()->getServiceLocator()->get('frontHelper')->dbshopHttpOrHttps();
            $baseUrl  = $e->getTarget()->getServiceLocator()->get('Request')->getbaseUrl();

            $userRegExtendFieldTable = $e->getTarget()->getServiceLocator()->get('UserRegExtendFieldTable');
            $fieldArray = $userRegExtendFieldTable->listUserRegExtendField(array('field_state'=>1));

            $showUserExtend = $this->createShowUserRegExtendField($fieldArray, array('http_host'=>$httpHost, 'http_type'=>$httpType, 'base_url'=>$baseUrl), $fieldValue);

            if(!empty($showUserExtend)) return $showUserExtend;
        }
    }
    /**
     * 保存会员扩展信息
     * @param Event $e
     */
    public function onSaveUserRegExtend(Event $e)
    {
        $values     = $e->getParam('values');

        $postArray  = $e->getTarget()->getServiceLocator()->get('request')->getPost()->toArray();
        $uploadClass= $e->getTarget()->getServiceLocator()->get('shop_other_upload');

        $userRegExtendFieldTable = $e->getTarget()->getServiceLocator()->get('UserRegExtendFieldTable');
        $fieldArray = $userRegExtendFieldTable->listUserRegExtendField(array('field_state'=>1));

        $insertArray= $this->createInsertUserRegField($fieldArray, $postArray, $uploadClass, $values['user_id']);

        if(!empty($insertArray)) {
            $userRegExtendTable = $e->getTarget()->getServiceLocator()->get('UserRegExtendTable');
            $regExtendInfo = $userRegExtendTable->infoUserRegExtend(array('user_id'=>$values['user_id']));
            if(isset($regExtendInfo->user_id) and $regExtendInfo->user_id > 0) {
                unset($insertArray['user_id']);
                if(!empty($insertArray)) $userRegExtendTable->editUserRegExtend($insertArray, array('user_id'=>$values['user_id']));
            } else {
                $userRegExtendTable->addUserRegExtend($insertArray);
            }
        }

    }
    /**
     * 删除会员扩展信息
     * @param Event $e
     */
    public function onDelUserRegExtend(Event $e)
    {
        $values = $e->getParam('values');
        $userRegExtendTable = $e->getTarget()->getServiceLocator()->get('UserRegExtendTable');
        $regExtendInfo = $userRegExtendTable->infoUserRegExtend(array('user_id'=>$values->user_id));

        $delState = $userRegExtendTable->delUserRegExtend(array('user_id'=>$values->user_id));
        if($delState) {
            $userRegExtendFieldTable = $e->getTarget()->getServiceLocator()->get('UserRegExtendFieldTable');
            $fieldArray = $userRegExtendFieldTable->listUserRegExtendField(array('field_type'=>'upload'));
            if(empty($fieldArray)) return ;
            foreach($fieldArray as $fValue) {
                if(!empty($regExtendInfo->$fValue['field_name'])) {
                    @unlink(DBSHOP_PATH . $regExtendInfo->$fValue['field_name']);
                }
            }
        }
    }
    /**
     * 批量删除客户扩展信息
     * @param Event $e
     */
    public function onAllDelUserRegExtend(Event $e)
    {
        $values = $e->getParam('values');

        $userRegExtendFieldTable = $e->getTarget()->getServiceLocator()->get('UserRegExtendFieldTable');
        $fieldArray = $userRegExtendFieldTable->listUserRegExtendField(array('field_type'=>'upload'));

        $userRegExtendTable = $e->getTarget()->getServiceLocator()->get('UserRegExtendTable');
        foreach($values as $userId) {
            $regExtendInfo = $userRegExtendTable->infoUserRegExtend(array('user_id'=>$userId));
            $delState = $userRegExtendTable->delUserRegExtend(array('user_id'=>$userId));
            if($delState and !empty($fieldArray)) {
                foreach($fieldArray as $fValue) {
                    if(!empty($regExtendInfo->$fValue['field_name'])) {
                        @unlink(DBSHOP_PATH . $regExtendInfo->$fValue['field_name']);
                    }
                }
            }
        }
    }
    /**
     * 获取提交内容
     * @param $fieldArray
     * @param $postArray
     * @param $uploadClass
     * @param $userId
     * @return array
     */
    private function createInsertUserRegField($fieldArray, $postArray, $uploadClass, $userId)
    {
        $insertArray = array();
        if(!empty($fieldArray)) {
            foreach($fieldArray as $fValue) {
                switch($fValue['field_type']) {
                    case 'text':
                    case 'select':
                    case 'radio':
                    case 'textarea':
                        if(isset($postArray[$fValue['field_name']]))
                            $insertArray[$fValue['field_name']] = !empty($postArray[$fValue['field_name']]) ? $postArray[$fValue['field_name']] : null;
                        break;

                    case 'checkbox':
                        if(isset($postArray[$fValue['field_name']]))
                            $insertArray[$fValue['field_name']] = !empty($postArray[$fValue['field_name']]) ? @implode(',', $postArray[$fValue['field_name']]) : null;
                        break;

                    case 'upload':
                        $uploadFile = $uploadClass->userRegExtendFileUpload($userId, $fValue['field_name'], $postArray['old_'.$fValue['field_name']]);
                        if(isset($uploadFile['image'])) $insertArray[$fValue['field_name']] = $uploadFile['image'];
                        break;
                }
            }
            $insertArray['user_id'] = $userId;
        }
        return $insertArray;
    }
    /**
     * 生成客户扩展表单
     * @param $fieldArray
     * @param $urlArray
     * @param null $fileValue
     * @return array
     */
    private function createUserRegExtendField($fieldArray, $urlArray, $fileValue=null)
    {
        $inputArray = array();
        $fileValue  = (array) $fileValue;
        $inputNotEmpty  = array();

        $jsStart = array();
        $jsEnd  = array();
        $jsCheckboxAndRadio = array();

        if(!empty($fieldArray)) {

            $notEmpty  = '';

            foreach($fieldArray as $fKey => $fValue) {
                $input = '';
                $inputBody = array();
                if(!empty($fValue)) {

                    $inputValue = isset($fileValue[$fValue['field_name']]) ? htmlentities($fileValue[$fValue['field_name']], ENT_QUOTES, "UTF-8") : '';

                    switch($fValue['field_type']) {
                        case 'text':
                            $input = '<input type="text" name="'.$fValue['field_name'].'" id="'.$fValue['field_name'].'" value="'.$inputValue.'" class="span3">';

                            if($fValue['field_null'] == 2) {//js判断空提示
                                $jsStart[] = $fValue['field_name'] . ':{required: true}';
                                $jsEnd[]= $fValue['field_name'] . ':{required: "'.$fValue['field_title'].'不能为空！"}';

                                $notEmpty = $fValue['field_title'].'不能为空！';
                                $inputNotEmpty[] = array(
                                    'id'  => $fValue['field_name'],
                                    'not_empty'  => $notEmpty,
                                );
                            }
                            break;

                        case 'select':
                            $checkboxArray = explode("\r\n", $fValue['field_radio_checkbox_select']);
                            $input = '<select name="'.$fValue['field_name'].'" id="'.$fValue['field_name'].'" class="span2">';
                            $input .= '<option value="">请选择</option>';
                            foreach($checkboxArray as $cValue) {
                                $keyAndValue = explode('=', $cValue);
                                $inputBody[] = $keyAndValue;
                                $input .= '<option value="'.$keyAndValue[0].'" '.((!empty($inputValue) and $inputValue == $keyAndValue[0]) ? 'selected' : '').'>'.$keyAndValue[1].'</option>';
                            }
                            $input = $input . '</select>';

                            if($fValue['field_null'] == 2) {//js判断空提示
                                $jsStart[] = $fValue['field_name'] . ':{required: true}';
                                $jsEnd[]= $fValue['field_name'] . ':{required: "请选择'.$fValue['field_title'].'！"}';

                                $notEmpty = $fValue['field_title'].'不能为空！';
                                $inputNotEmpty[] = array(
                                    'id'  => $fValue['field_name'],
                                    'not_empty'  => $notEmpty,
                                );
                            }
                            break;

                        case 'checkbox':
                            $checkboxArray = explode("\r\n", $fValue['field_radio_checkbox_select']);
                            $checkedArray  = !empty($inputValue) ? explode(',', $inputValue) : array();

                            foreach($checkboxArray as $cValue) {
                                $keyAndValue = explode('=', $cValue);
                                $inputBody[] = $keyAndValue;
                                $input .= '<label class="checkbox inline"><input '.((!empty($checkedArray) and in_array($keyAndValue[0], $checkedArray)) ? 'checked' : '').' type="checkbox" name="'.$fValue['field_name'].'[]" value="'.$keyAndValue[0].'">'.$keyAndValue[1].'</label>';
                            }

                            if($fValue['field_null'] == 2) {//js判断空提示
                                $notEmpty = 'var check_'.$fValue['field_name'].'_state=false;$("input[name=\''.$fValue['field_name'].'[]\']").each(function() {if (this.checked == true) {check_'.$fValue['field_name'].'_state = true;}});if(check_'.$fValue['field_name'].'_state == false) {alert("'.$fValue['field_title'].'不能为空！");return false;}';
                                $jsCheckboxAndRadio[] = $notEmpty;
                            }
                            break;

                        case 'radio':
                            $checkboxArray = explode("\r\n", $fValue['field_radio_checkbox_select']);
                            foreach($checkboxArray as $cKey => $cValue) {
                                $keyAndValue = explode('=', $cValue);
                                $inputBody[] = $keyAndValue;
                                $input .= '<label class="radio inline"><input '.(!empty($inputValue) ? ($inputValue==$keyAndValue[0] ? 'checked' : '') : ($cKey == 0 ? 'checked' : '')).' type="radio" name="'.$fValue['field_name'].'" value="'.$keyAndValue[0].'">'.$keyAndValue[1].'</label>';
                            }

                            if($fValue['field_null'] == 2) {//js判断空提示
                                $notEmpty = 'var check_'.$fValue['field_name'].'=$(\'input:radio[name="'.$fValue['field_name'].'"]:checked\').val();if(check_'.$fValue['field_name'].'==null){alert("'.$fValue['field_title'].'不能为空！");return false;}';
                                $jsCheckboxAndRadio[] = $notEmpty;
                            }
                            break;

                        case 'textarea':
                            $input = '<textarea name="'.$fValue['field_name'].'" id="'.$fValue['field_name'].'" class="span3" rows="4">'.$inputValue.'</textarea>';

                            if($fValue['field_null'] == 2) {//js判断空提示
                                $jsStart[] = $fValue['field_name'] . ':{required: true}';
                                $jsEnd[]= $fValue['field_name'] . ':{required: "'.$fValue['field_title'].'不能为空！"}';

                                $notEmpty = $fValue['field_title'].'不能为空！';
                                $inputNotEmpty[] = array(
                                    'id'  => $fValue['field_name'],
                                    'not_empty'  => $notEmpty,
                                );
                            }
                            break;

                        case 'upload':
                            $input = '<input type="file" name="'.$fValue['field_name'].'" id="'.$fValue['field_name'].'">';
                            if(!empty($inputValue)) {
                                $inputFile = '<a href="'.$urlArray['base_url'].$inputValue.'" target="_blank" ><img src="'.$urlArray['base_url'].$inputValue.'" style="height:100px" ></a>';
                                $inputFile.= '<input type="hidden" name="old_'.$fValue['field_name'].'" value="'.$inputValue.'" ><br>';
                                $input = $inputFile . $input;
                            }
                            if($fValue['field_null'] == 2 and empty($inputValue)) {//js判断空提示
                                $jsStart[] = $fValue['field_name'] . ':{required: true}';
                                $jsEnd[]= $fValue['field_name'] . ':{required: "'.$fValue['field_title'].'不能为空！"}';

                                $notEmpty = $fValue['field_title'].'不能为空！';
                                $inputNotEmpty[] = array(
                                    'id'  => $fValue['field_name'],
                                    'not_empty'  => $notEmpty,
                                );
                            }
                            break;
                    }
                }

                $inputArray[] = array(
                    'field_title'   => $fValue['field_title'],
                    'field_type'    => $fValue['field_type'],
                    'field_null'    => $fValue['field_null'],
                    'input'         => $input,

                    //当使用非jquery兼用验证的时候，利用下面的内容进行具体化编写
                    'name'          => $fValue['field_name'],
                    'id'            => $fValue['field_name'],
                    'not_empty'     => $notEmpty,
                    'body'          => $inputBody,//当表单类型为 select、radio、checkbox 时 有内容
                    'input_value'   => $inputValue,//已有内容
                );
            }
        }

        return array('inputArray'=>$inputArray, 'inputNotEmpty'=>$inputNotEmpty, 'jsCheck'=>array('jsStart' => $jsStart, 'jsEnd' => $jsEnd, 'jsCheckboxAndRadio' => $jsCheckboxAndRadio));
    }
    /**
     * 将扩展信息进行显示处理，用于前台调用会员信息时显示
     * @param $fieldArray
     * @param $urlArray
     * @param $fileValue
     * @return array
     */
    private function createShowUserRegExtendField($fieldArray, $urlArray, $fileValue)
    {
        $valueArray = array();
        $fileValue  = (array) $fileValue;

        if(!empty($fieldArray)) {
            foreach($fieldArray as $fKey => $fValue) {
                if(!empty($fValue)) {

                    $inputValue = isset($fileValue[$fValue['field_name']]) ? htmlentities($fileValue[$fValue['field_name']], ENT_QUOTES, "UTF-8") : '';

                    switch($fValue['field_type']) {
                        case 'text':
                            $valueArray[$fValue['field_name']] = !empty($inputValue) ? $inputValue : '';;
                            break;

                        case 'select':
                            $checkboxArray = explode("\r\n", $fValue['field_radio_checkbox_select']);
                            foreach($checkboxArray as $cValue) {
                                $keyAndValue = explode('=', $cValue);
                                if((!empty($inputValue) and $inputValue == $keyAndValue[0])) {
                                    $valueArray[$fValue['field_name']] = $keyAndValue[1];
                                    break;
                                }
                            }

                            if(!isset($valueArray[$fValue['field_name']])) $valueArray[$fValue['field_name']] = '';
                            break;

                        case 'checkbox':
                            $checkboxArray = explode("\r\n", $fValue['field_radio_checkbox_select']);
                            $checkedArray  = !empty($inputValue) ? explode(',', $inputValue) : array();

                            foreach($checkboxArray as $cValue) {
                                $keyAndValue = explode('=', $cValue);
                                if(!empty($checkedArray) and in_array($keyAndValue[0], $checkedArray)) {
                                    $valueArray[$fValue['field_name']] .= $keyAndValue[1].' ';
                                }
                            }

                            if(!isset($valueArray[$fValue['field_name']])) $valueArray[$fValue['field_name']] = '';
                            break;

                        case 'radio':
                            $checkboxArray = explode("\r\n", $fValue['field_radio_checkbox_select']);
                            foreach($checkboxArray as $cKey => $cValue) {
                                $keyAndValue = explode('=', $cValue);
                                if((!empty($inputValue) and $inputValue == $keyAndValue[0])) {
                                    $valueArray[$fValue['field_name']] = $keyAndValue[1];
                                    break;
                                }
                            }
                            if(!isset($valueArray[$fValue['field_name']])) $valueArray[$fValue['field_name']] = '';
                            break;

                        case 'textarea':
                            $valueArray[$fValue['field_name']] = !empty($inputValue) ? $inputValue : '';
                            break;

                        case 'upload':
                            $valueArray[$fValue['field_name']] = !empty($inputValue) ? $urlArray['http_type'].$urlArray['http_host'].$urlArray['base_url'].$inputValue : '';
                            break;
                    }
                }
            }
        }

        return array('user_extend'=>$valueArray);
    }
    /**
     * 后台配送订单当缴纳财务时，对优惠券进行判断生效处理
     * @param Event $e
     */
    public function onBackExpressFinishUpdateUserCoupon(Event $e)
    {
        $values = $e->getParam('values');
        $courierOrder = $values['courier_order'];
        $stateArray   = $values['state_array'];
        if($courierOrder->order_id and $courierOrder->order_id > 0 and !empty($stateArray)) {
            $orderTable = $e->getTarget()->getServiceLocator()->get('OrderTable');

            $orderInfo = $orderTable->infoOrder(array('order_id'=>$courierOrder->order_id));
            //已完成（缴纳财务）
            if(isset($stateArray['delivery_state_6'])) {
                $userCouponTable = $e->getTarget()->getServiceLocator()->get('UserCouponTable');
                if($orderInfo->buyer_id > 0) {
                    $userCouponArray = $userCouponTable->listUserCoupon(array('coupon_use_state'=>'0', 'get_order_id'=>$courierOrder->order_id, 'user_id'=>$orderInfo->buyer_id));
                    if(is_array($userCouponArray) and !empty($userCouponArray)) {
                        foreach($userCouponArray as $couponValue) {
                            if($couponValue['coupon_use_state'] == 0) {
                                $userCouponTable->updateUserCoupon(array('coupon_use_state'=>1), array('user_coupon_id'=>$couponValue['user_coupon_id'], 'get_order_id'=>$courierOrder->order_id, 'user_id'=>$orderInfo->buyer_id));
                            }
                        }
                    }
                }
            }
        }
    }
    /**
     * 订单完成时(前台+后台)，对于会员优惠券的有效修改
     * @param Event $e
     */
    public function onUpdateUserCoupon(Event $e)
    {
        $orderId = $e->getParam('values');
        if(is_numeric($orderId) and $orderId > 0) {
            $userCouponTable = $e->getTarget()->getServiceLocator()->get('UserCouponTable');
            $orderTable = $e->getTarget()->getServiceLocator()->get('OrderTable');

            $orderInfo = $orderTable->infoOrder(array('order_id'=>$orderId));
            if($orderInfo->buyer_id > 0) {
                $userCouponArray = $userCouponTable->listUserCoupon(array('coupon_use_state'=>'0', 'get_order_id'=>$orderId, 'user_id'=>$orderInfo->buyer_id));
                if(is_array($userCouponArray) and !empty($userCouponArray)) {
                    foreach($userCouponArray as $couponValue) {
                        if($couponValue['coupon_use_state'] == 0) {
                            $userCouponTable->updateUserCoupon(array('coupon_use_state'=>1), array('user_coupon_id'=>$couponValue['user_coupon_id'], 'get_order_id'=>$orderInfo->order_id, 'user_id'=>$orderInfo->buyer_id));
                        }
                    }
                }
            }
        }
    }
    /**
     * 会员点击获取
     * @param Event $e
     */
    public function clickGetUserCoupon(Event $e)
    {
        $couponRule = $this->getCouponRuleIni();
        if(empty($couponRule)) return ;

        $userCouponTable = $e->getTarget()->getServiceLocator()->get('UserCouponTable');
    }
    /**
     * 注册会员获取优惠券
     * @param Event $e
     */
    public function registerGetUserCoupon(Event $e)
    {
        $couponRule = $this->getCouponRuleIni();
        if(empty($couponRule)) return ;

        $other = $e->getParam('values');
        if(empty($other) or !is_array($other)) return;

        $userCouponTable = $e->getTarget()->getServiceLocator()->get('UserCouponTable');

        $data = array(
            'coupon_ini'        =>$couponRule,
            'user_id'           => $other['user_id'],
            'user_name'         => $other['user_name'],
            'userCouponTable'   => $userCouponTable,
        );

        foreach ($data['coupon_ini'] as $ruleKey => $ruleValue) {
            if($ruleValue['coupon_state'] == '2') continue;
            if(!empty($ruleValue['get_coupon_start_time']) and !empty($ruleValue['get_coupon_end_time']) and $ruleValue['get_coupon_start_time']>$ruleValue['get_coupon_end_time']) continue;
            if(!empty($ruleValue['get_coupon_end_time']) and $ruleValue['get_coupon_end_time'] < time()) continue;
            if($ruleValue['get_coupon_type'] != 'register') continue;

            $userCouponInfo = $data['userCouponTable']->infoUserCoupon(array('coupon_id'=>$ruleValue['coupon_id'], 'user_id'=>$data['user_id']));
            if($userCouponInfo) continue;//如果该优惠券已经在该用户优惠券中存在，则不做处理
            $addUserCoupon = array(
                'coupon_use_state'  => 1,                   //优惠券状态，注册状态获取的优惠码直接可用
                'user_id'           => $data['user_id'],    //会员id
                'user_name'         => $data['user_name'],  //会员名称
                'get_time'          => time(),              //获取时间
                'coupon_id'         => $ruleValue['coupon_id'],     //优惠券id
                'coupon_name'       => $ruleValue['coupon_name'],   //优惠券名称

                'coupon_info'       => $ruleValue['coupon_info'],//优惠券描述
            );
            if(!empty($ruleValue['coupon_start_time'])) $addUserCoupon['coupon_start_use_time'] = $ruleValue['coupon_start_time'];
            if(!empty($ruleValue['coupon_end_time'])) $addUserCoupon['coupon_expire_time'] = $ruleValue['coupon_end_time'];

            $data['userCouponTable']->addUserCoupon($addUserCoupon);
        }
    }
    /**
     * 删除会员的同时，删除其名下的优惠券
     * @param Event $e
     */
    public function onDelUserCoupon(Event $e)
    {
        $values = $e->getParam('values');
        if(isset($values->user_id) and $values->user_id > 0) {
            $userCouponTable = $e->getTarget()->getServiceLocator()->get('UserCouponTable');
            $userCouponTable->delUserCoupon(array('user_id'=>$values->user_id));
        }
    }
    /**
     * 删除会员的同时，删除其名下的优惠券(批量删除会员时)
     * @param Event $e
     */
    public function onAllDelUserCoupon(Event $e)
    {
        $values = $e->getParam('values');
        if(is_array($values) and !empty($values)) {
            $userCouponTable = $e->getTarget()->getServiceLocator()->get('UserCouponTable');
            $userCouponTable->delUserCoupon(array('user_id IN ('.implode(',', $values).')'));
        }
    }
    /**
     * 购物获取优惠券（下单的时候进行判断获取）
     * @param Event $e
     */
    public function buyGetUserCoupon(Event $e)
    {
        $couponRule = $this->getCouponRuleIni();
        if(empty($couponRule)) return ;

        $other = $e->getParam('other');
        if(empty($other) or !is_array($other)) return;

        $userCouponTable = $e->getTarget()->getServiceLocator()->get('UserCouponTable');

        $data = array(
            'coupon_ini'        =>$couponRule,
            'user_id'           => $other['user_id'],
            'user_name'         => $other['user_name'],
            'user_group'        => $other['user_group'],
            'cartGoods'         => $other['cartGoods'],
            'userCouponTable'   => $userCouponTable,
            'order_id'          => $other['order_id'],
            'order_sn'          => $other['order_sn']
        );

        foreach ($data['coupon_ini'] as $ruleKey => $ruleValue) {
            if($ruleValue['coupon_state'] == '2') continue;
            if(!empty($ruleValue['get_coupon_start_time']) and !empty($ruleValue['get_coupon_end_time']) and $ruleValue['get_coupon_start_time']>$ruleValue['get_coupon_end_time']) continue;
            if(!empty($ruleValue['get_coupon_start_time']) and $ruleValue['get_coupon_start_time'] > time()) continue;
            if(!empty($ruleValue['get_coupon_end_time']) and $ruleValue['get_coupon_end_time'] < time()) continue;
            if($ruleValue['get_coupon_type'] != 'buy') continue;

            //获取符合条件的商品价格总和，并与优惠规则中的各个规则进行匹配
            $costTotal = $this->cartCostTotal(array('cartGoods'=>$data['cartGoods'], 'rule'=>$ruleValue, 'user_group'=>$data['user_group']));

            if($costTotal >= $ruleValue['get_shopping_amount']) {
                $userCouponInfo = $data['userCouponTable']->infoUserCoupon(array('coupon_id'=>$ruleValue['coupon_id'], 'user_id'=>$data['user_id']));
                if($userCouponInfo) continue;//如果该优惠券已经在该用户优惠券中存在，则不做处理

                $addUserCoupon = array(
                    'coupon_use_state'  => 0,                   //优惠券状态
                    'user_id'           => $data['user_id'],    //会员id
                    'user_name'         => $data['user_name'],  //会员名称
                    'get_time'          => time(),              //获取时间
                    'coupon_id'         => $ruleValue['coupon_id'],     //优惠券id
                    'coupon_name'       => $ruleValue['coupon_name'],   //优惠券名称

                    'get_order_id'      => $data['order_id'],   //购买商品的该订单id
                    'get_order_sn'      => $data['order_sn'],   //购买商品的该订单编号
                    'coupon_info'       => $ruleValue['coupon_info'],//优惠券描述
                );
                if(!empty($ruleValue['coupon_start_time'])) $addUserCoupon['coupon_start_use_time'] = $ruleValue['coupon_start_time'];
                if(!empty($ruleValue['coupon_end_time'])) $addUserCoupon['coupon_expire_time'] = $ruleValue['coupon_end_time'];

                $data['userCouponTable']->addUserCoupon($addUserCoupon);
            }
        }
    }
    /**
     * 当为购买商品获取优惠券时，用该方法计算符合条件的商品购物总额
     * @param array $data
     * @return int
     */
    private function cartCostTotal(array $data)
    {
        $costTotal = 0;
        foreach ($data['cartGoods'] as $cartValue) {
            //所有用户所有商品
            if($data['rule']['get_user_type'] == 'all_user' and $data['rule']['get_goods_type'] == 'all_goods') {
                $costTotal = $costTotal + $cartValue['goods_shop_price'] * $cartValue['buy_num'];
                continue;
                //客户组，所有商品
            } elseif ($data['rule']['get_user_type'] == 'user_group' and $data['rule']['get_goods_type'] == 'all_goods') {
                if(!empty($data['user_group']) and isset($data['rule']['get_user_group']) and is_array($data['rule']['get_user_group']) and !empty($data['rule']['get_user_group'])) {
                    if(in_array($data['user_group'], $data['rule']['get_user_group'])) {
                        $costTotal = $costTotal + $cartValue['goods_shop_price'] * $cartValue['buy_num'];
                        continue;
                    }
                }
                //所有用户，商品分类
            } elseif ($data['rule']['get_user_type'] == 'all_user' and $data['rule']['get_goods_type'] == 'class_goods') {
                if(isset($data['rule']['get_coupon_goods_body']) and is_array($data['rule']['get_coupon_goods_body']) and !empty($data['rule']['get_coupon_goods_body'])) {
                    foreach ($cartValue['class_id_array'] as $class_id) {
                        if(in_array($class_id, $data['rule']['get_coupon_goods_body'])) {
                            $costTotal = $costTotal + $cartValue['goods_shop_price'] * $cartValue['buy_num'];
                            continue 2;
                        }
                    }
                }
                //客户组，商品分类
            } elseif ($data['rule']['get_user_type'] == 'user_group' and $data['rule']['get_goods_type'] == 'class_goods') {
                $user_group_state  = false;
                $goods_class_state = false;
                if(!empty($data['user_group']) and isset($data['rule']['get_user_group']) and is_array($data['rule']['get_user_group']) and !empty($data['rule']['get_user_group'])) {
                    if(in_array($data['user_group'], $data['rule']['get_user_group'])) {
                        $user_group_state = true;
                    }
                }
                if(isset($data['rule']['get_coupon_goods_body']) and is_array($data['rule']['get_coupon_goods_body']) and !empty($data['rule']['get_coupon_goods_body'])) {
                    foreach ($cartValue['class_id_array'] as $class_id) {
                        if(in_array($class_id, $data['rule']['get_coupon_goods_body'])) {
                            $goods_class_state = true;
                            break;
                        }
                    }
                }
                if($user_group_state and $goods_class_state) {
                    $costTotal = $costTotal + $cartValue['goods_shop_price'] * $cartValue['buy_num'];
                    continue;
                }
                //所有用户，商品品牌
            } elseif ($data['rule']['get_user_type'] == 'all_user' and $data['rule']['get_goods_type'] == 'brand_goods') {
                if(!empty($cartValue['brand_id']) and isset($data['rule']['get_coupon_goods_body']) and is_array($data['rule']['get_coupon_goods_body']) and !empty($data['rule']['get_coupon_goods_body'])) {
                    if(in_array($cartValue['brand_id'], $data['rule']['get_coupon_goods_body'])) {
                        $costTotal = $costTotal + $cartValue['goods_shop_price'] * $cartValue['buy_num'];
                        continue;
                    }
                }
                //客户组，商品品牌
            } elseif ($data['rule']['get_user_type'] == 'user_group' and $data['rule']['get_goods_type'] == 'brand_goods') {
                $user_group_state  = false;
                $goods_brand_state = false;
                if(!empty($data['user_group']) and isset($data['rule']['get_user_group']) and is_array($data['rule']['get_user_group']) and !empty($data['rule']['get_user_group'])) {
                    if(in_array($data['user_group'], $data['rule']['get_user_group'])) {
                        $user_group_state = true;
                    }
                }
                if(!empty($cartValue['brand_id']) and isset($data['rule']['get_coupon_goods_body']) and is_array($data['rule']['get_coupon_goods_body']) and !empty($data['rule']['get_coupon_goods_body'])) {
                    if(in_array($cartValue['brand_id'], $data['rule']['get_coupon_goods_body'])) $goods_brand_state = true;
                }
                if($user_group_state and $goods_brand_state) {
                    $costTotal = $costTotal + $cartValue['goods_shop_price'] * $cartValue['buy_num'];
                    continue;
                }
            }
        }
        return $costTotal;
    }
    /**
     * 获取优惠规则
     * @return array
     */
    private function getCouponRuleIni()
    {
        $iniReader  = new Ini();
        $couponRule = array();

        $iniPath    = DBSHOP_PATH . '/data/moduledata/System/coupon_rule.ini';
        if(file_exists($iniPath)) {
            $couponRule = $iniReader->fromFile($iniPath);
        }

        return $couponRule;
    }
}