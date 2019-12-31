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

namespace Orders\Controller;

use Admin\Controller\BaseController;
use Zend\View\Model\ViewModel;

class OrdersController extends BaseController
{
    public function indexAction()
    {
        $array = array();
        
        $searchArray  = array();
        if($this->request->isGet()) {
            $searchArray = $this->request->getQuery()->toArray();
        }
        //订单列表
        $page = $this->params('page',1);
        $array['order_list'] = $this->getDbshopTable()->listOrder(array('page'=>$page, 'page_num'=>20), $searchArray);
        $array['page']= $page;
        $array['searchArray'] = $searchArray;

        $array['express_array'] = $this->getDbshopTable('ExpressTable')->listExpress();
        $array['region_array']= $this->getDbshopTable('RegionTable')->listRegion(array('dbshop_region.region_top_id=0'));

        return $array;
    }
    /** 
     * 编辑查看订单
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|multitype:number NULL
     */
    public function editAction ()
    {
        $array = array();
        
        $array['page'] = (int) $this->params('page', 1);
        $orderId       = (int) $this->params('order_id');
        $array['query']= $this->request->getQuery()->toArray();
        //订单信息
        $array['order_info'] = $this->getDbshopTable()->infoOrder(array('order_id'=>$orderId));
        if(!$array['order_info']) return $this->redirect()->toRoute('orders/default');
        
        //订单配送信息
        $array['delivery_address'] = $this->getDbshopTable('OrderDeliveryAddressTable')->infoDeliveryAddress(array('order_id'=>$orderId));
        
        //订单商品
        $array['order_goods'] = $this->getDbshopTable('OrderGoodsTable')->listOrderGoods(array('order_id'=>$orderId));
        //退货信息
        if($array['order_info']->refund_state == 1) {
            $array['refund_order']=$this->getDbshopTable('OrderRefundTable')->infoOrderRefund(array('order_sn'=>$array['order_info']->order_sn));
        }
        //订单操作历史
        $array['order_log'] = $this->getDbshopTable('OrderLogTable')->listOrderLog(array('order_id'=>$orderId));
        
        //物流状态信息
        if($array['order_info']['order_state'] >= 40 and $array['delivery_address']['express_number'] != '') {
            $iniReader   = new \Zend\Config\Reader\Ini();
            $expressPath = DBSHOP_PATH . '/data/moduledata/Express/';
            if(file_exists($expressPath . $array['order_info']['express_id'] . '.ini')) {
                $expressIni = $iniReader->fromFile($expressPath . $array['order_info']['express_id'] . '.ini');
                $array['express_url'] = isset($expressIni['express_url']) ? $expressIni['express_url'] : '';
                if(is_array($expressIni) and isset($expressIni['express_name_code']) and $expressIni['express_name_code'] != '' and file_exists($expressPath . 'express.php')) {
                    $expressArray = include($expressPath . 'express.php');
                    if(!empty($expressArray)) {
                        $array['express_state_array'] = $this->getServiceLocator()->get('shop_express_state')->getExpressStateContent($expressArray, $expressIni['express_name_code'], $array['delivery_address']['express_number']);
                    }
                }
            }
        }

        //订单总价修改历史
        $array['order_amount_log'] = $this->getDbshopTable('OrderAmountLogTable')->listOrderAmountLog(array('order_id'=>$orderId));

        return $array;
    }
    /** 
     * 订单打印
     */
    public function orderprintAction ()
    {
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $array     = array();
        
        $orderId = (int) $this->params('order_id');
        //订单信息
        $array['order_info'] = $this->getDbshopTable()->infoOrder(array('order_id'=>$orderId));
        if(!$array['order_info']) return $this->redirect()->toRoute('orders/default');
        
        //订单配送信息
        $array['delivery_address'] = $this->getDbshopTable('OrderDeliveryAddressTable')->infoDeliveryAddress(array('order_id'=>$orderId));
        
        //订单商品
        $array['order_goods'] = $this->getDbshopTable('OrderGoodsTable')->listOrderGoods(array('order_id'=>$orderId));

        //订单总价修改历史
        $array['order_amount_log'] = $this->getDbshopTable('OrderAmountLogTable')->listOrderAmountLog(array('order_id'=>$orderId));

        $viewModel->setVariables($array);
        return $viewModel;
    }
    /** 
     * 订单支付状态修改
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|multitype:number NULL
     */
    public function payoperAction ()
    {
        $array = array();
        
        $array['page'] = (int) $this->params('page', 1);
        $orderId = (int) $this->params('order_id');
        
        //订单信息
        $array['order_info'] = $this->getDbshopTable()->infoOrder(array('order_id'=>$orderId));
        $orderInfo = $array['order_info'];

        if($this->request->isPost()) {
            $stateArray = $this->request->getPost()->toArray();
            if($stateArray['pay_state'] != $array['order_info']->order_state) {
                $hdfkPayState = true;//货到付款的状态
                if($array['order_info']->order_state >= 40 and $array['order_info']->pay_code == 'hdfk' and empty($array['order_info']->pay_time)) {
                    $hdfkPayState = false;
                } else {
                    $this->getDbshopTable()->updateOrder(array('order_state'=>$stateArray['pay_state']), array('order_id'=>$orderId));
                }

                if($stateArray['pay_state'] == 20 or !$hdfkPayState) {//付款完成

                    $payTime = time();
                    $this->getDbshopTable()->updateOrder(array('pay_time'=>$payTime), array('order_id'=>$orderId));
                    /*----------------------付款完成提醒信息发送----------------------*/
                    $deliveryAddress = $this->getDbshopTable('OrderDeliveryAddressTable')->infoDeliveryAddress(array('order_id'=>$orderId));
                    $sendArray['buyer_name']  = $array['order_info']->buyer_name;
                    $sendArray['order_sn']    = $array['order_info']->order_sn;
                    $sendArray['order_total'] = $array['order_info']->order_amount;
                    $sendArray['express_name']  = isset($deliveryAddress->express_name) ? $deliveryAddress->express_name : '';
                    $sendArray['express_number']= isset($deliveryAddress->express_number) ? $deliveryAddress->express_number : '';
                    $sendArray['time']        = $payTime;
                    $sendArray['buyer_email'] = $array['order_info']->buyer_email;
                    $sendArray['order_state'] = 'payment_finish';
                    $sendArray['time_type']   = 'paymenttime';
                    $sendArray['subject']     = $this->getDbshopLang()->translate('订单付款完成');
                    $this->changeStateSendMail($sendArray);
                    /*----------------------付款完成提醒信息发送----------------------*/

                    /*----------------------手机提醒信息发送----------------------*/
                    $smsData = array(
                        'buyname'  => $sendArray['buyer_name'],
                        'ordersn'    => $sendArray['order_sn'],
                        'ordertotal'   => $sendArray['order_total'],
                        'time'        => $sendArray['time'],
                    );
                    try {
                        $userInfo = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$array['order_info']->buyer_id));
                        $this->getServiceLocator()->get('shop_send_sms')->toSendSms(
                            $smsData,
                            $userInfo->user_phone,
                            'alidayu_payment_order_template_id',
                            $array['order_info']->buyer_id
                        );
                    } catch(\Exception $e) {

                    }
                    /*----------------------手机提醒信息发送----------------------*/
                    //事件驱动
                    $this->getEventManager()->trigger('order.pay.backstage.post', $this, array('values'=>$orderId));
                }
                
                $this->getDbshopTable('OrderLogTable')->addOrderLog(array('order_id'=>$orderId, 'order_state'=>$stateArray['pay_state'], 'state_info'=>$stateArray['state_info'], 'log_time'=>time(), 'log_user'=>$this->getServiceLocator()->get('adminHelper')->returnAuth('admin_name')));
                
                //记录操作日志
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('订单管理'), 'operlog_info'=>$this->getDbshopLang()->translate('更新订单支付状态') . '&nbsp;' . $array['order_info']->order_sn . ' : ' . $this->getServiceLocator()->get('frontHelper')->getOneOrderStateInfo($stateArray['pay_state'])));
                
                return $this->redirect()->toRoute('orders/default/order_id',array('action'=>'edit','controller'=>'Orders','order_id'=>$orderId,'page'=>$array['page']));
            }
        }
        
        return $array;
    }
    /** 
     * 配送状态修改
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|multitype:number NULL
     */
    public function shipoperAction ()
    {
        $array = array();
        
        $array['page'] = (int) $this->params('page', 1);
        $orderId = (int) $this->params('order_id');
        $array['url_type'] = $this->request->getQuery('url_type');//此为接收从发货单查看过来的信息
        //订单信息
        $array['order_info'] = $this->getDbshopTable()->infoOrder(array('order_id'=>$orderId));
        //快递单号
        $array['express_number'] = $this->getDbshopTable('ExpressNumberTable')->oneExpressNumber(array('express_id'=>$array['order_info']->express_id, 'express_number_state'=>0));
        //订单配送信息
        $array['delivery_address'] = $this->getDbshopTable('OrderDeliveryAddressTable')->infoDeliveryAddress(array('order_id'=>$orderId));

        if($this->request->isPost()) {
            $stateArray = $this->request->getPost()->toArray();
            if($stateArray['ship_state'] != $array['order_info']->order_state) {
                //支付方式中的发货处理
                if(isset($array['delivery_address']) and !empty($array['delivery_address'])) {
                    $array['delivery_address']->express_number = (isset($stateArray['express_number']) ? $stateArray['express_number'] : 'no-expressnumber');
                    if(!$this->getServiceLocator()->get('payment')->payServiceSet($array['order_info']->pay_code)->toSendOrder($array['order_info'], $array['delivery_address'])) exit('false');
                }
                
                $this->getDbshopTable()->updateOrder(array('order_state'=>$stateArray['ship_state']), array('order_id'=>$orderId));
                //当是发货时，编辑发货时间
                $expressTime = time();
                if($stateArray['ship_state'] == 40) {
                    $this->getDbshopTable()->updateOrder(array('express_time'=>$expressTime), array('order_id'=>$orderId));
                }
                //当有快递单号时，进行编辑
                if(isset($stateArray['express_number']) and $stateArray['express_number'] != '') {
                    $this->getDbshopTable('OrderDeliveryAddressTable')->updateDeliveryAddress(array('express_number'=>$stateArray['express_number']), array('order_id'=>$orderId, 'express_id'=>$array['delivery_address']->express_id));
                    //判断是否在快递单数据表中有，如果有且未被使用的，使用
                    if($this->getDbshopTable('ExpressNumberTable')->infoExpressNumber(array('express_id'=>$array['order_info']->express_id, 'express_number'=>$stateArray['express_number'], 'express_number_state'=>0))) {
                        $updateExpressNumberArray = array();
                        $updateExpressNumberArray['order_id'] = $array['order_info']->order_id;
                        $updateExpressNumberArray['order_sn'] = $array['order_info']->order_sn;
                        $updateExpressNumberArray['express_number_state'] = 1;
                        $updateExpressNumberArray['express_number_use_time'] = time();
                        $this->getDbshopTable('ExpressNumberTable')->updateExpressNumber($updateExpressNumberArray, array('express_id'=>$array['order_info']->express_id, 'express_number'=>$stateArray['express_number'], 'express_number_state'=>0));
                    }
                }
                //查看订单中是否有虚拟商品，进行虚拟商品发货
                $virtualGoodsArray = $this->getDbshopTable('OrderGoodsTable')->listOrderGoods(array('order_id'=>$orderId, 'goods_type'=>2));
                if(is_array($virtualGoodsArray) and !empty($virtualGoodsArray)) {
                    foreach($virtualGoodsArray as $orderVirtualGoods) {
                        if(isset($orderVirtualGoods['buy_num']) and $orderVirtualGoods['buy_num'] > 0) {
                            //判断是否已经部分商品是否已经发货，如果已经发货则跳过，进行下一个商品的处理
                            $vGoodsNum_2 = $this->getDbshopTable('VirtualGoodsTable')->countVirtualGoods(array('goods_id'=>$orderVirtualGoods['goods_id'], 'order_sn'=>$array['order_info']->order_sn, 'virtual_goods_state'=>2));
                            if($vGoodsNum_2 >= $orderVirtualGoods['buy_num']) continue;
                            //如果部分商品已经发货，可是该商品没有足够的库存，只是发了库存中有的，那么在这里的处理，要减去已经发出的商品库存
                            if($vGoodsNum_2 > 0) $orderVirtualGoods['buy_num'] = $orderVirtualGoods['buy_num'] - $vGoodsNum_2;

                            for($i=0; $i<$orderVirtualGoods['buy_num']; $i++) {
                                $virtualGoodsInfo = $this->getDbshopTable('VirtualGoodsTable')->infoVirtualGoods(array('goods_id'=>$orderVirtualGoods['goods_id'], 'virtual_goods_state'=>1));
                                if(isset($virtualGoodsInfo[0]) and is_array($virtualGoodsInfo[0]) and !empty($virtualGoodsInfo[0])) {
                                    $updateVirtualGoods = array();
                                    $updateVirtualGoods['order_sn'] = $array['order_info']->order_sn;
                                    $updateVirtualGoods['virtual_goods_state'] = 2;
                                    $updateVirtualGoods['order_id'] = $array['order_info']->order_id;
                                    $updateVirtualGoods['user_id']  = $array['order_info']->buyer_id;
                                    $updateVirtualGoods['user_name']= $array['order_info']->buyer_name;

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

                                        $this->getDbshopTable('VirtualGoodsTable')->addVirtualGoods($updateVirtualGoods);
                                    } else {
                                        $this->getDbshopTable('VirtualGoodsTable')->updateVirtualGoods($updateVirtualGoods, array('virtual_goods_id'=>$virtualGoodsInfo[0]['virtual_goods_id']));
                                    }
                                }
                            }
                        }
                    }
                }

                //保存订单历史
                $this->getDbshopTable('OrderLogTable')->addOrderLog(array('order_id'=>$orderId, 'order_state'=>$stateArray['ship_state'], 'state_info'=>$stateArray['state_info'], 'log_time'=>$expressTime, 'log_user'=>$this->getServiceLocator()->get('adminHelper')->returnAuth('admin_name')));
                
                //记录操作日志
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('订单管理'), 'operlog_info'=>$this->getDbshopLang()->translate('更新订单配送状态') . '&nbsp;' . $array['order_info']->order_sn . ' : ' . $this->getServiceLocator()->get('frontHelper')->getOneOrderStateInfo($stateArray['ship_state'])));
                
                /*----------------------提醒信息发送----------------------*/
                //订单配送信息
                $deliveryAddress = $this->getDbshopTable('OrderDeliveryAddressTable')->infoDeliveryAddress(array('order_id'=>$orderId));

                $sendArray['buyer_name']  = $array['order_info']->buyer_name;
                $sendArray['order_sn']    = $array['order_info']->order_sn;
                $sendArray['order_total'] = $array['order_info']->order_amount;
                $sendArray['express_name']  = isset($deliveryAddress->express_name) ? $deliveryAddress->express_name : '';
                $sendArray['express_number']= isset($deliveryAddress->express_number) ? $deliveryAddress->express_number : '';
                $sendArray['time']        = $expressTime;
                $sendArray['buyer_email'] = $array['order_info']->buyer_email;
                $sendArray['order_state'] = 'ship_finish';
                $sendArray['time_type']   = 'shiptime';
                $sendArray['subject']     = $this->getDbshopLang()->translate('订单发货完成|管理员操作');
                $this->changeStateSendMail($sendArray);
                /*----------------------提醒信息发送----------------------*/

                /*----------------------手机提醒信息发送----------------------*/
                $smsData = array(
                    'buyname'  => $sendArray['buyer_name'],
                    'ordersn'    => $sendArray['order_sn'],
                    'ordertotal'   => $sendArray['order_total'],
                    'expressname'  => $sendArray['express_name'],
                    'expressnumber'=> $sendArray['express_number'],
                    'time'        => $sendArray['time'],
                );
                try {
                    $userInfo = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$array['order_info']->buyer_id));
                    $this->getServiceLocator()->get('shop_send_sms')->toSendSms(
                        $smsData,
                        $userInfo->user_phone,
                        'alidayu_ship_order_template_id',
                        $array['order_info']->buyer_id
                    );
                } catch(\Exception $e) {

                }
                /*----------------------手机提醒信息发送----------------------*/

                if($stateArray['ship_state'] == 40) {
                    //事件驱动
                    $this->getEventManager()->trigger('order.deliver.backstage.post', $this, array('values'=>$orderId));
                }

                if($array['url_type'] == 'show_ship') {//从发货单过来的
                    return $this->redirect()->toRoute('orders/default/order_id',array('action'=>'showShip','controller'=>'Orders','order_id'=>$orderId,'page'=>$array['page']));
                } else {
                    return $this->redirect()->toRoute('orders/default/order_id',array('action'=>'edit','controller'=>'Orders','order_id'=>$orderId,'page'=>$array['page']));
                }
            }
        }

        $array['virtual_all_goods'] = 'yes';//假定全部是虚拟商品
        //如果订单中有实物商品
        $vGoodsInfo = $this->getDbshopTable('OrderGoodsTable')->InfoOrderGoods(array('order_id'=>$orderId, 'goods_type'=>1));
        if(!empty($vGoodsInfo)) {//说明订单商品中有实物商品
            $array['virtual_all_goods'] = 'no';
        }
        //查看订单中是否有虚拟商品，同时查看是否有库存不足的问题
        $vGoodsArray = $this->getDbshopTable('OrderGoodsTable')->listOrderGoods(array('order_id'=>$orderId, 'goods_type'=>2));
        $vGoodsStockFew = array();
        if(!empty($vGoodsArray)) {
            foreach($vGoodsArray as $vGoodsValue) {
                $virtualGoodsInfo = $this->getDbshopTable('VirtualGoodsTable')->infoVirtualGoods(array('goods_id'=>$vGoodsValue['goods_id'], 'virtual_goods_state'=>1));
                $virtualGoodsAccountType    = isset($virtualGoodsInfo[0]['virtual_goods_account_type']) ? $virtualGoodsInfo[0]['virtual_goods_account_type'] : '';
                $virtualGoodsPasswordType   = isset($virtualGoodsInfo[0]['virtual_goods_password_type']) ? $virtualGoodsInfo[0]['virtual_goods_password_type'] : '';
                if(($virtualGoodsAccountType == 1 and $virtualGoodsPasswordType == 1) or empty($virtualGoodsInfo)) {
                    if(isset($vGoodsValue['buy_num']) and $vGoodsValue['buy_num'] > 0) {
                        //可用的虚拟商品数量
                        $vGoodsNum_1 = $this->getDbshopTable('VirtualGoodsTable')->countVirtualGoods(array('goods_id'=>$vGoodsValue['goods_id'], 'virtual_goods_state'=>1));
                        //已经在该订单使用的虚拟商品数量
                        $vGoodsNum_2 = $this->getDbshopTable('VirtualGoodsTable')->countVirtualGoods(array('goods_id'=>$vGoodsValue['goods_id'], 'order_sn'=>$array['order_info']->order_sn, 'virtual_goods_state'=>2));
                        //购买的虚拟商品数量
                        $buyVirtualNum = $vGoodsValue['buy_num'] - $vGoodsNum_2;
                        if($buyVirtualNum > $vGoodsNum_1) {
                            $vGoodsStockFew[] = array(
                                'goods_name' => $vGoodsValue['goods_name'],
                                'num' => $buyVirtualNum - $vGoodsNum_1,
                            );
                        }
                    }
                }

            }
        }
        $array['v_stock_few'] = $vGoodsStockFew;

        return $array;
    }
    /** 
     * 订单完成状态
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|multitype:number NULL
     */
    public function finishoperAction ()
    {
        $array = array();
        
        $array['page'] = (int) $this->params('page', 1);
        $orderId = (int) $this->params('order_id');
        
        //订单信息
        $array['order_info'] = $this->getDbshopTable()->infoOrder(array('order_id'=>$orderId));
        if($this->request->isPost()) {
            $stateArray = $this->request->getPost()->toArray();
            if($stateArray['order_state'] != $array['order_info']->order_state and !empty($array['order_info']->pay_time) and $array['order_info']->order_state == 40) {
                $finishTime =time();
                $this->getDbshopTable()->updateOrder(array('order_state'=>$stateArray['order_state'], 'finish_time'=>$finishTime), array('order_id'=>$orderId));
                //事件驱动
                $this->getEventManager()->trigger('order.finish.backstage.post', $this, array('values'=>$orderId));
                //保存订单历史
                $this->getDbshopTable('OrderLogTable')->addOrderLog(array('order_id'=>$orderId, 'order_state'=>$stateArray['order_state'], 'state_info'=>$stateArray['state_info'], 'log_time'=>$finishTime, 'log_user'=>$this->getServiceLocator()->get('adminHelper')->returnAuth('admin_name')));
                
                //记录操作日志
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('订单管理'), 'operlog_info'=>$this->getDbshopLang()->translate('更新订单状态') . '&nbsp;' . $array['order_info']->order_sn . ' : ' . $this->getServiceLocator()->get('frontHelper')->getOneOrderStateInfo($stateArray['order_state'])));

                //积分获取
                if($array['order_info']->integral_num > 0 or $array['order_info']->integral_type_2_num > 0) {
                    $integralLogArray = array();
                    $integralLogArray['user_id']           = $array['order_info']->buyer_id;
                    $integralLogArray['user_name']         = $array['order_info']->buyer_name;
                    $integralLogArray['integral_log_info'] = $this->getDbshopLang()->translate('商品购物，订单号为：') . $array['order_info']->order_sn . '<br>';
                    $integralLogArray['integral_log_time'] = time();

                    if($array['order_info']->integral_num > 0) {//消费积分
                        $integralLogArray['integral_num_log']  = $array['order_info']->integral_num;
                        $integralLogArray['integral_log_info'] .= $array['order_info']->integral_rule_info;
                        if($this->getDbshopTable('IntegralLogTable')->addIntegralLog($integralLogArray)) {
                            //会员消费积分更新
                            $this->getDbshopTable('UserTable')->updateUserIntegralNum($integralLogArray, array('user_id'=>$array['order_info']->buyer_id));
                        }
                    }
                    if($array['order_info']->integral_type_2_num > 0) {//等级积分
                        $integralLogArray['integral_num_log']  = $array['order_info']->integral_type_2_num;
                        $integralLogArray['integral_log_info'] .= $array['order_info']->integral_type_2_num_rule_info;
                        $integralLogArray['integral_type_id'] = 2;
                        if($this->getDbshopTable('IntegralLogTable')->addIntegralLog($integralLogArray)) {
                            //会员等级积分更新
                            $this->getDbshopTable('UserTable')->updateUserIntegralNum($integralLogArray, array('user_id'=>$array['order_info']->buyer_id), 2);
                        }
                    }
                }
                /*----------------------提醒信息发送----------------------*/
                //订单配送信息
                $deliveryAddress = $this->getDbshopTable('OrderDeliveryAddressTable')->infoDeliveryAddress(array('order_id'=>$orderId));

                $sendArray['buyer_name']  = $array['order_info']->buyer_name;
                $sendArray['order_sn']    = $array['order_info']->order_sn;
                $sendArray['order_total'] = $array['order_info']->order_amount;
                $sendArray['express_name']  = $deliveryAddress->express_name;
                $sendArray['express_number']= $deliveryAddress->express_number;
                $sendArray['time']        = $finishTime;
                $sendArray['buyer_email'] = $array['order_info']->buyer_email;
                $sendArray['order_state'] = 'transaction_finish';
                $sendArray['time_type']   = 'finishtime';
                $sendArray['subject']     = $this->getDbshopLang()->translate('订单交易完成|管理员操作');
                $this->changeStateSendMail($sendArray);
                /*----------------------提醒信息发送----------------------*/

                /*----------------------手机提醒信息发送----------------------*/
                $smsData = array(
                    'buyname'  => $sendArray['buyer_name'],
                    'ordersn'    => $sendArray['order_sn'],
                    'ordertotal'   => $sendArray['order_total'],
                    'expressname'  => $sendArray['express_name'],
                    'expressnumber'=> $sendArray['express_number'],
                    'time'        => $sendArray['time'],
                );
                try {
                    $userInfo = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$array['order_info']->buyer_id));
                    $this->getServiceLocator()->get('shop_send_sms')->toSendSms(
                        $smsData,
                        $userInfo->user_phone,
                        'alidayu_finish_order_template_id',
                        $array['order_info']->buyer_id
                    );
                } catch(\Exception $e) {

                }
                /*----------------------手机提醒信息发送----------------------*/

                return $this->redirect()->toRoute('orders/default/order_id',array('action'=>'edit','controller'=>'Orders','order_id'=>$orderId,'page'=>$array['page']));
            }
        }
        
        return $array;
    }
    /**
     * 订单批量处理，删除
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function editOrderAllAction ()
    {
        if($this->request->isPost()) {
            $orderIdArray = $this->request->getPost('order_id');
            if(is_array($orderIdArray) and !empty($orderIdArray)) {
                $orderSnArray = array();
                $idArray = array();
                foreach ($orderIdArray as $idValue) {
                    $orderInfo  = $this->getDbshopTable('OrderTable')->infoOrder(array('order_id'=>$idValue));
                    if($orderInfo->order_state == 0) {//订单状态为取消状态
                        $this->getDbshopTable('OrderTable')->delOrder(array('order_id'=>$idValue));
                        $orderSnArray[] = $orderInfo->order_sn;
                        $idArray[] = $orderInfo->order_id;
                    }
                }
                //事件驱动
                $this->getEventManager()->trigger('order.del.backstage.post', $this, array('values'=>$idArray, 'orderSn' => $orderSnArray));

                if(!empty($orderSnArray)) {
                    //记录操作日志
                    $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('订单管理'), 'operlog_info'=>$this->getDbshopLang()->translate('订单批量删除') . '&nbsp;' . implode(' ', $orderSnArray)));
                }
            }
        }
        return $this->redirect()->toRoute('orders/default');
    }
    /** 
     * 取消订单操作
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function cancelOrderAction ()
    {
        $array['page'] = (int) $this->params('page', 1);
        $orderId       = (int) $this->params('order_id');

        if($orderId == 0) return $this->redirect()->toRoute('orders/default');
        
        $orderInfo  = $this->getDbshopTable('OrderTable')->infoOrder(array('order_id'=>$orderId));
        if($this->request->isPost()) {
            $stateArray = $this->request->getPost()->toArray();

            if($orderInfo and ($orderInfo->order_state == 10 or ($orderInfo->order_state == 30 and $orderInfo->pay_code == 'hdfk'))) {
                $this->getDbshopTable('OrderTable')->updateOrder(array('order_state'=>0), array('order_id'=>$orderId));
                //记录操作日志
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('订单管理'), 'operlog_info'=>$this->getDbshopLang()->translate('更新订单状态') . '&nbsp;' . $orderInfo->order_sn . ' : ' . $this->getServiceLocator()->get('frontHelper')->getOneOrderStateInfo(0)));
                //取消订单对库存进行返回
                $this->operGoodsStock($orderId);
                //检查是否有消费积分付款
                if($orderInfo->integral_buy_num > 0) {
                    $integralLogArray = array();
                    $integralLogArray['user_id']           = $orderInfo->buyer_id;
                    $integralLogArray['user_name']         = $orderInfo->buyer_name;
                    $integralLogArray['integral_log_info'] = $this->getDbshopLang()->translate('取消订单，订单号为：') . $orderInfo->order_sn;
                    $integralLogArray['integral_num_log']  = $orderInfo->integral_buy_num;
                    $integralLogArray['integral_log_time'] = time();
                    if($this->getDbshopTable('IntegralLogTable')->addIntegralLog($integralLogArray)) {
                        //会员消费积分更新
                        $this->getDbshopTable('UserTable')->updateUserIntegralNum($integralLogArray, array('user_id'=>$integralLogArray['user_id']));
                    }
                }
                //加入状态记录
                $stateArray['state_info'] = (!empty($stateArray['state_info']) ? trim($stateArray['state_info']) : $this->getDbshopLang()->translate('无说明'));
                $this->getDbshopTable('OrderLogTable')->addOrderLog(array('order_id'=>$orderId, 'order_state'=>'0', 'state_info'=>$stateArray['state_info'], 'log_time'=>time(), 'log_user'=>$this->getServiceLocator()->get('adminHelper')->returnAuth('admin_name')));

                /*----------------------提醒信息发送----------------------*/
                $sendArray['buyer_name']  = $orderInfo->buyer_name;
                $sendArray['order_sn']    = $orderInfo->order_sn;
                $sendArray['order_total'] = $orderInfo->order_amount;
                $sendArray['time']        = time();
                $sendArray['buyer_email'] = $orderInfo->buyer_email;
                $sendArray['order_state'] = 'cancel_order';
                $sendArray['time_type']   = 'canceltime';
                $sendArray['cancel_info'] = $stateArray['state_info'];
                $sendArray['subject']     = $this->getDbshopLang()->translate('订单取消|管理员操作');
                $this->changeStateSendMail($sendArray);
                /*----------------------提醒信息发送----------------------*/

                /*----------------------手机提醒信息发送----------------------*/
                $smsData = array(
                    'buyname'  => $sendArray['buyer_name'],
                    'ordersn'    => $sendArray['order_sn'],
                    'ordertotal'   => $sendArray['order_total'],
                    'time'        => $sendArray['time'],
                );
                try {
                    $userInfo = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$orderInfo->buyer_id));
                    $this->getServiceLocator()->get('shop_send_sms')->toSendSms(
                        $smsData,
                        $userInfo->user_phone,
                        'alidayu_cancel_order_template_id',
                        $orderInfo->buyer_id
                    );
                } catch(\Exception $e) {

                }
                /*----------------------手机提醒信息发送----------------------*/

                $this->getEventManager()->trigger('order.cancel.backstage.post', $this, array('values'=>$orderId));
            }
            return $this->redirect()->toRoute('orders/default/order_id',array('action'=>'edit','controller'=>'Orders','order_id'=>$orderId,'page'=>$array['page']));
        }

        return array('order_info'=>$orderInfo);
    }
    /**
     * 退货管理
     * @return array
     */
    public function refundAction()
    {
        $array = array();
        $array['page'] = (int) $this->params('page', 1);

        $searchArray  = array();
        if($this->request->isGet()) {
            $searchArray = $this->request->getQuery()->toArray();
        }
        $array['searchArray'] = $searchArray;
        $page = $array['page'];
        $array['order_refund_list'] = $this->getDbshopTable('OrderRefundTable')->listOrderRefund(array('page'=>$page, 'page_num'=>20), $searchArray);

        return $array;
    }
    /**
     * 退货处理页面
     */
    public function operRefundAction()
    {
        $array = array();
        $array['page'] = (int) $this->params('page', 1);
        $array['query']= $this->request->getQuery()->toArray();
        $refundId = (int) $this->params('refund_id');

        $array['refund_info'] = $this->getDbshopTable('OrderRefundTable')->infoOrderRefund(array('refund_id'=>$refundId));

        if($this->request->isPost()) {

            if($array['refund_info']->refund_state != 0) return $this->redirect()->toRoute('orders/default/refund-id',array('action'=>'refund', 'controller'=>'Orders', 'page'=>$array['page']));

            $refundArray = $this->request->getPost()->toArray();

            $this->getDbshopTable('dbshopTransaction')->DbshopTransactionBegin();
            try {
                $updateArray['refund_price']   = floatval($refundArray['refund_price']);
                $updateArray['refund_state']   = intval($refundArray['refund_state']);
                $updateArray['re_refund_info'] = trim($refundArray['re_refund_info']);
                $updateArray['finish_refund_time'] = time();
                $updateArray['admin_id']       = $this->getServiceLocator()->get('adminHelper')->returnAuth('admin_id');
                $updateArray['admin_name']     = $this->getServiceLocator()->get('adminHelper')->returnAuth('admin_name');
                $state = $this->getDbshopTable('OrderRefundTable')->updateOrderRefund($updateArray, array('refund_id'=>$refundId));

                if($state) {//退款到账户余额
                    $userInfo = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$array['refund_info']->user_id));

                    if($updateArray['refund_state'] == 1 and $array['refund_info']->refund_type == 1) {
                        $moneyLogArray = array();
                        $moneyLogArray['user_id']         = $userInfo->user_id;
                        $moneyLogArray['user_name']       = $userInfo->user_name;
                        $moneyLogArray['money_change_num']= $updateArray['refund_price'];
                        $moneyLogArray['money_pay_state'] = 20;//20是已经处理（充值后者减值，10是待处理）
                        $moneyLogArray['money_pay_type']  = 4;//支付类型，1充值，2消费，3提现，4退款
                        $moneyLogArray['admin_id']        = $updateArray['admin_id'];
                        $moneyLogArray['admin_name']      = $updateArray['admin_name'];
                        $moneyLogArray['money_changed_amount'] = $userInfo->user_money + $moneyLogArray['money_change_num'];
                        $moneyLogArray['money_pay_info']  = $this->getDbshopLang()->translate('订单退货').' '.$this->getDbshopLang()->translate('退货订单编号为：').$array['refund_info']->order_sn;

                        $this->getDbshopTable('UserMoneyLogTable')->addUserMoneyLog($moneyLogArray);

                        //对会员表中的余额总值进行更新
                        $this->getDbshopTable('UserTable')->updateUser(array('user_money'=>$moneyLogArray['money_changed_amount']), array('user_id'=>$userInfo->user_id));
                    }
                    if($updateArray['refund_state'] == 1) {//如果是同意退货，则对订单进行设置
                        $this->getDbshopTable('OrderTable')->updateOrder(array('refund_state'=>1), array('order_sn'=>$array['refund_info']->order_sn));
                    }
                    //操作日志记录
                    $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('退货管理'), 'operlog_info'=>$this->getDbshopLang()->translate('退货订单处理').' '.$this->getDbshopLang()->translate('退货状态:').($updateArray['refund_state']==1 ? $this->getDbshopLang()->translate('同意退货') : $this->getDbshopLang()->translate('拒绝退货')).' '.$this->getDbshopLang()->translate('退货的订单编号为:').$array['refund_info']->order_sn));
                    $this->getDbshopTable('dbshopTransaction')->DbshopTransactionCommit();
                } else $this->getDbshopTable('dbshopTransaction')->DbshopTransactionRollback();
            } catch (\Exception $e) {
                $this->getDbshopTable('dbshopTransaction')->DbshopTransactionRollback();
            }

            return $this->redirect()->toRoute('orders/default/refund-id',array('action'=>'refund', 'controller'=>'Orders', 'page'=>$array['page']));
        }

        $array['order_info']  = $this->getDbshopTable('OrderTable')->infoOrder(array('order_sn'=>$array['refund_info']->order_sn));
        //订单商品
        $array['order_goods'] = $this->getDbshopTable('OrderGoodsTable')->listOrderGoods(array('order_id'=> $array['order_info']->order_id));
        //订单总价修改历史
        $array['order_amount_log'] = $this->getDbshopTable('OrderAmountLogTable')->listOrderAmountLog(array('order_id'=>$array['order_info']->order_id));
        return $array;
    }
    /**
     * 退货详情查看
     * @return array
     */
    public function showRefundAction()
    {
        $array = array();
        $array['page'] = (int) $this->params('page', 1);
        $array['query']= $this->request->getQuery()->toArray();
        $refundId = (int) $this->params('refund_id');

        $array['refund_info'] = $this->getDbshopTable('OrderRefundTable')->infoOrderRefund(array('refund_id'=>$refundId));

        $array['order_info']  = $this->getDbshopTable('OrderTable')->infoOrder(array('order_sn'=>$array['refund_info']->order_sn));
        //订单商品
        $array['order_goods'] = $this->getDbshopTable('OrderGoodsTable')->listOrderGoods(array('order_id'=> $array['order_info']->order_id));
        //订单总价修改历史
        $array['order_amount_log'] = $this->getDbshopTable('OrderAmountLogTable')->listOrderAmountLog(array('order_id'=>$array['order_info']->order_id));
        return $array;
    }
    /**
     * 管理员的退货操作（货到付款 方式的处理）
     */
    public function adminRefundAction()
    {
        $array = array();

        $array['page']  = (int) $this->params('page', 1);
        $orderId        = (int) $this->params('order_id');

        $array['type']  = $this->request->getQuery('type');
        if(!in_array($array['type'], array('refund_goods', 'refund_fee', 'refund_goodsandfee'))) return $this->redirect()->toRoute('orders/default');

        //订单信息
        $array['order_info'] = $this->getDbshopTable()->infoOrder(array('order_id'=>$orderId));
        if(!$array['order_info']) return $this->redirect()->toRoute('orders/default');

        if($this->request->isPost()) {
            $stateArray = $this->request->getPost()->toArray();
            $orderInfo   = $array['order_info'];
            $type        = $array['type'];
            $refundMessage = array('refund_goods'=>'退货', 'refund_fee'=>'退款', 'refund_goodsandfee'=>'退货退款');

            if($orderInfo and $orderInfo->order_state < 60 and $orderInfo->order_state > 10) {
                $this->getDbshopTable('OrderTable')->updateOrder(array('order_state'=>0), array('order_id'=>$orderId));
                //记录操作日志
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('订单管理'), 'operlog_info'=>sprintf($this->getDbshopLang()->translate('更新(%s)订单状态'), $refundMessage[$type]) . '&nbsp;' . $orderInfo->order_sn . ' : ' . $this->getServiceLocator()->get('frontHelper')->getOneOrderStateInfo(0)));
                //取消订单对库存进行返回
                $this->operGoodsStock($orderId);
                //当是退款或者退款退货时，将订单金额退到账户余额
                if(in_array($type, array('refund_fee', 'refund_goodsandfee'))) {
                    $userInfo = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$orderInfo->buyer_id));
                    $moneyLogArray = array();
                    $moneyLogArray['user_id']         = $userInfo->user_id;
                    $moneyLogArray['user_name']       = $userInfo->user_name;
                    $moneyLogArray['money_change_num']= $orderInfo->order_amount;
                    $moneyLogArray['money_pay_state'] = 20;//20是已经处理（充值后者减值，10是待处理）
                    $moneyLogArray['money_pay_type']  = 4;//支付类型，1充值，2消费，3提现，4退款
                    $moneyLogArray['admin_id']        = $this->getServiceLocator()->get('adminHelper')->returnAuth('admin_id');
                    $moneyLogArray['admin_name']      = $this->getServiceLocator()->get('adminHelper')->returnAuth('admin_name');
                    $moneyLogArray['money_changed_amount'] = $userInfo->user_money + $moneyLogArray['money_change_num'];
                    $moneyLogArray['money_pay_info']  = $this->getDbshopLang()->translate('订单退款').' '.$this->getDbshopLang()->translate('退款订单编号为：').$orderInfo->order_sn;

                    $this->getDbshopTable('UserMoneyLogTable')->addUserMoneyLog($moneyLogArray);

                    //对会员表中的余额总值进行更新
                    $this->getDbshopTable('UserTable')->updateUser(array('user_money'=>$moneyLogArray['money_changed_amount']), array('user_id'=>$userInfo->user_id));

                    $this->getDbshopTable('OrderTable')->updateOrder(array('refund_state'=>1), array('order_sn'=>$array['order_info']->order_sn));
                }
                //检查是否有消费积分付款
                if($orderInfo->integral_buy_num > 0) {
                    $integralLogArray = array();
                    $integralLogArray['user_id']           = $orderInfo->buyer_id;
                    $integralLogArray['user_name']         = $orderInfo->buyer_name;
                    $integralLogArray['integral_log_info'] = sprintf($this->getDbshopLang()->translate('取消(%s)订单，订单号为：'), $refundMessage[$type]) . $orderInfo->order_sn;
                    $integralLogArray['integral_num_log']  = $orderInfo->integral_buy_num;
                    $integralLogArray['integral_log_time'] = time();
                    if($this->getDbshopTable('IntegralLogTable')->addIntegralLog($integralLogArray)) {
                        //会员消费积分更新
                        $this->getDbshopTable('UserTable')->updateUserIntegralNum($integralLogArray, array('user_id'=>$integralLogArray['user_id']));
                    }
                }
                //加入状态记录
                $stateArray['state_info'] = (!empty($stateArray['state_info']) ? trim($stateArray['state_info']) : $this->getDbshopLang()->translate('无说明')).'-'.$refundMessage[$type];
                $this->getDbshopTable('OrderLogTable')->addOrderLog(array('order_id'=>$orderId, 'order_state'=>'0', 'state_info'=>$stateArray['state_info'], 'log_time'=>time(), 'log_user'=>$this->getServiceLocator()->get('adminHelper')->returnAuth('admin_name')));

                /*----------------------提醒信息发送----------------------*/
                $sendArray['buyer_name']  = $orderInfo->buyer_name;
                $sendArray['order_sn']    = $orderInfo->order_sn;
                $sendArray['order_total'] = $orderInfo->order_amount;
                $sendArray['time']        = time();
                $sendArray['buyer_email'] = $orderInfo->buyer_email;
                $sendArray['order_state'] = 'cancel_order';
                $sendArray['time_type']   = 'canceltime';
                $sendArray['cancel_info'] = $stateArray['state_info'];
                $sendArray['subject']     = sprintf($this->getDbshopLang()->translate('订单取消(%s)|管理员操作'), $refundMessage[$type]);
                $this->changeStateSendMail($sendArray);
                /*----------------------提醒信息发送----------------------*/

                /*----------------------手机提醒信息发送----------------------*/
                $smsData = array(
                    'buyname'  => $sendArray['buyer_name'],
                    'ordersn'    => $sendArray['order_sn'],
                    'ordertotal'   => $sendArray['order_total'],
                    'time'        => $sendArray['time'],
                );
                try {
                    $userInfo = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$orderInfo->buyer_id));
                    $this->getServiceLocator()->get('shop_send_sms')->toSendSms(
                        $smsData,
                        $userInfo->user_phone,
                        'alidayu_cancel_order_template_id',
                        $orderInfo->buyer_id
                    );
                } catch(\Exception $e) {

                }
                /*----------------------手机提醒信息发送----------------------*/
            }
            return $this->redirect()->toRoute('orders/default/order_id',array('action'=>'edit','controller'=>'Orders','order_id'=>$orderId,'page'=>$array['page']));
        }

        //订单配送信息
        $array['delivery_address'] = $this->getDbshopTable('OrderDeliveryAddressTable')->infoDeliveryAddress(array('order_id'=>$orderId));
        //订单商品
        $array['order_goods'] = $this->getDbshopTable('OrderGoodsTable')->listOrderGoods(array('order_id'=>$orderId));

        return $array;
    }
    /**
     * 商品库存操作
     * @param unknown $orderId
     */
    private function operGoodsStock ($orderId)
    {
        $goodsArray = $this->getDbshopTable('OrderGoodsTable')->listOrderGoods(array('order_id'=>$orderId));
        if(is_array($goodsArray) and !empty($goodsArray)) {
            foreach ($goodsArray as $goodsValue) {
                $goodsInfo = '';
                $goodsInfo = $this->getDbshopTable('GoodsTable')->oneGoodsInfo(array('goods_id'=>$goodsValue['goods_id']));
                if($goodsInfo->goods_stock_state_open != 1) {//如果没有启用库存状态显示
                    if((!empty($goodsValue['goods_color']) and !empty($goodsValue['goods_size'])) || !empty($goodsValue['goods_spec_tag_id'])) {
                        if(!empty($goodsValue['goods_spec_tag_id'])) $whereExtend = array('goods_id'=>$goodsValue['goods_id'], 'adv_spec_tag_id'=>$goodsValue['goods_spec_tag_id']);
                        else $whereExtend = array('goods_id'=>$goodsValue['goods_id'], 'goods_color'=>$goodsValue['goods_color'], 'goods_size'=>$goodsValue['goods_size']);

                        $extendGoods = $this->getDbshopTable('GoodsPriceExtendGoodsTable')->InfoPriceExtendGoods($whereExtend);
                        $this->getDbshopTable('GoodsPriceExtendGoodsTable')->updatePriceExtendGoods(array('goods_extend_stock'=>($extendGoods->goods_extend_stock + $goodsValue['buy_num'])), $whereExtend);
                    } else {
                        $this->getDbshopTable('GoodsTable')->oneUpdateGoods(array('goods_stock'=>($goodsInfo->goods_stock + $goodsValue['buy_num'])), array('goods_id'=>$goodsValue['goods_id']));
                    }
                }
            }
        }
    }
    /** 
     * 订单删除，必须是取消的订单才可以删除
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function delOrderAction ()
    {
        $orderId    = (int) $this->params('order_id');
        if($orderId == 0) return $this->redirect()->toRoute('orders/default');
        
        $orderInfo  = $this->getDbshopTable('OrderTable')->infoOrder(array('order_id'=>$orderId));
        if($orderInfo->order_state == 0) {
            $this->getDbshopTable('OrderTable')->delOrder(array('order_id'=>$orderId));
            //事件驱动
            $this->getEventManager()->trigger('order.del.backstage.post', $this, array('values'=>array($orderId), 'orderSn' => array($orderInfo->order_sn)));

            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('订单管理'), 'operlog_info'=>$this->getDbshopLang()->translate('订单删除') . '&nbsp;' . $orderInfo->order_sn));
        }
        
        return $this->redirect()->toRoute('orders/default');
    }
    /** 
     * 会员编辑调用订单列表
     * @return string|Ambigous <\Zend\View\Model\ViewModel, \Zend\View\Model\ViewModel>
     */
    public function ajaxOrderListAction ()
    {
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        
        $array        = array();
        $searchArray  = array();
        
        $buyerId      = $this->params('buyer_id', 0);
        if($buyerId == 0) {
            return '';
        }
        if($buyerId != 0) {
            $searchArray['buyer_id'] = $buyerId;
            $array['user_id']        = $buyerId;
        }
        $array['show_div_id']    = $this->request->getQuery('show_div_id');
        //订单列表
        $page = $this->params('page',1);
        $array['order_list'] = $this->getDbshopTable()->listOrder(array('page'=>$page, 'page_num'=>20), $searchArray);
        
        return $viewModel->setVariables($array);
    }
    /**
     * 订单变更发送邮件
     * @param array $data
     */
    private function changeStateSendMail(array $data)
    {
        $sendMessageBody = $this->getServiceLocator()->get('frontHelper')->getSendMessageBody($data['order_state']);
        if($sendMessageBody != '') {
            $sendArray = array();
            $sendArray['shopname']      = $this->getServiceLocator()->get('frontHelper')->websiteInfo('shop_name');
            $sendArray['buyname']       = $data['buyer_name'];
            $sendArray['ordersn']       = $data['order_sn'];
            $sendArray['ordertotal']    = isset($data['order_total'])    ? $data['order_total'] : '';
            $sendArray['expressname']   = isset($data['express_name'])   ? $data['express_name'] : '';
            $sendArray['expressnumber'] = isset($data['express_number']) ? $data['express_number'] : '';
            $sendArray[$data['time_type']]= $data['time'];
            $sendArray['shopurl']       = $this->getServiceLocator()->get('frontHelper')->dbshopHttpOrHttps() . $this->getServiceLocator()->get('frontHelper')->dbshopHttpHost() . $this->url()->fromRoute('shopfront/default');

            $sendArray['cancel_info']   = (isset($data['cancel_info']) and !empty($data['cancel_info'])) ? $data['cancel_info'] : '';

            $sendArray['subject']       = $sendArray['shopname'] . $data['subject'];
            $sendArray['send_mail'][]   = $this->getServiceLocator()->get('frontHelper')->getSendMessageBuyerEmail($data['order_state'] . '_state', $data['buyer_email']);
            $sendArray['send_mail'][]   = $this->getServiceLocator()->get('frontHelper')->getSendMessageAdminEmail($data['order_state'] . '_state');
            
            $sendMessageBody            = $this->getServiceLocator()->get('frontHelper')->createSendMessageContent($sendArray, $sendMessageBody);
            try {
                $sendState = $this->getServiceLocator()->get('shop_send_mail')->SendMesssageMail($sendArray, $sendMessageBody);
                $sendState = ($sendState ? 1 : 2);
            } catch (\Exception $e) {
                $sendState = 2;
            }
            //记录给用户发的电邮
            if($sendArray['send_mail'][0] != '') {
                $userInfo = $this->getDbshopTable('UserTable')->infoUser(array('user_name'=>$sendArray['buyname']));
                $sendLog = array(
                        'mail_subject' => $sendArray['subject'],
                        'mail_body'    => $sendMessageBody,
                        'send_time'    => time(),
                        'user_id'      => $userInfo->user_id,
                        'send_state'   => $sendState
                );
                $this->getDbshopTable('UserMailLogTable')->addUserMailLog($sendLog);
            }
        }
    }
    /** 
     * 发货单列表
     * @return multitype:
     */
    public function shiplistAction()
    {
        $array = array();
        //发货列表
        $page = $this->params('page',1);
        $array['ship_list'] = $this->getDbshopTable('OrderDeliveryAddressTable')->listDeliveryAddress(array('page'=>$page, 'page_num'=>20), array('o.order_state >= 20 or o.pay_code="hdfk"'), array('o.order_id DESC'));
        $array['page']= $page;
        
        return $array;
    }
    /** 
     * 发货单查看
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|multitype:number NULL Ambigous <multitype:>
     */
    public function showShipAction()
    {
        $array = array();
        
        $array['page'] = (int) $this->params('page', 1);
        $orderId = (int) $this->params('order_id');
        //订单信息
        $array['order_info'] = $this->getDbshopTable()->infoOrder(array('order_id'=>$orderId));
        if(!$array['order_info']) return $this->redirect()->toRoute('orders/default', array('action'=>'shiplist', 'controller'=>'Orders'));
        
        //订单配送信息
        $array['delivery_address'] = $this->getDbshopTable('OrderDeliveryAddressTable')->infoDeliveryAddress(array('order_id'=>$orderId));
        
        //订单商品
        $array['order_goods'] = $this->getDbshopTable('OrderGoodsTable')->listOrderGoods(array('order_id'=>$orderId));
        
        //订单操作历史
        $array['order_log'] = $this->getDbshopTable('OrderLogTable')->listOrderLog(array('order_id'=>$orderId));
        
        //物流状态信息
        if($array['order_info']['order_state'] >= 40 and $array['delivery_address']['express_number'] != '') {
            $iniReader   = new \Zend\Config\Reader\Ini();
            $expressPath = DBSHOP_PATH . '/data/moduledata/Express/';
            if(file_exists($expressPath . $array['order_info']['express_id'] . '.ini')) {
                $expressIni = $iniReader->fromFile($expressPath . $array['order_info']['express_id'] . '.ini');
                $array['express_url'] = isset($expressIni['express_url']) ? $expressIni['express_url'] : '';
                if(is_array($expressIni) and isset($expressIni['express_name_code']) and $expressIni['express_name_code'] != '' and file_exists($expressPath . 'express.php')) {
                    $expressArray = include($expressPath . 'express.php');
                    if(!empty($expressArray)) {
                        $array['express_state_array'] = $this->getServiceLocator()->get('shop_express_state')->getExpressStateContent($expressArray, $expressIni['express_name_code'], $array['delivery_address']['express_number']);
                    }
                }
            }
        }
        
        return $array;
    }
    /**
     * 导出发货单
     * @return array
     */
    public function exportShipAction()
    {
        $array = array();

        if($this->request->isPost()) {
            $exportArray = $this->request->getPost()->toArray();
            $shipArray   = $this->getExportShipArray($exportArray);
            if(is_array($shipArray) and !empty($shipArray)) {
                $this->exportShipExcel($shipArray, $exportArray);
            }
        }

        $array['express_array'] = $this->getDbshopTable('ExpressTable')->listExpress();

        return $array;
    }
    /** 
     * 支付記錄
     * @return multitype:NULL
     */
    public function paylogAction()
    {
        $array = array();
        
        //支付列表
        $page = $this->params('page',1);
        $array['pay_list'] = $this->getDbshopTable()->listOrder(array('page'=>$page, 'page_num'=>20), array(), array('dbshop_order.order_state>=20'));

        return $array;
    }

    /**
     * 修改快递订单
     */
    public function saveOrderExpressNumberAction()
    {
        $orderId = (int) $this->params('order_id');
        $state   = 'false';
        if($this->request->isPost()) {
            $postArray = $this->request->getPost()->toArray();
            $orderInfo = $this->getDbshopTable()->infoOrder(array('order_id'=>$orderId));
            if($orderInfo->order_state < 60 or $orderInfo->order_state > 0) {
                $updateExpress = array(
                    'express_number' => $postArray['express_number']
                );
                $this->getDbshopTable('OrderDeliveryAddressTable')->updateDeliveryAddress($updateExpress, array('order_id'=>$orderId));
                $state = 'true';
            }
        }
        exit($state);
    }

    /**
     * 修改订单金额，添加历史
     */
    public function saveorderamountAction()
    {
        $orderId = (int) $this->params('order_id');
        $state   = 'false';
        if($this->request->isPost()) {
            $postArray = $this->request->getPost()->toArray();
            if($postArray['order_id'] == $orderId) {
                $orderInfo = $this->getDbshopTable()->infoOrder(array('order_id'=>$orderId));
                if($orderInfo->order_state < 15 or ($orderInfo->order_state == 30 and $orderInfo->pay_code == 'hdfk')) {
                    if($postArray['order_edit_amount_type'] == '-' and $postArray['order_edit_amount'] > $orderInfo->order_amount) {
                        exit('false');
                    }

                    $postArray['order_edit_amount'] = $postArray['order_edit_amount_type'].$postArray['order_edit_amount'];

                    $orderAmountLog = array();
                    $orderAmountLog['order_edit_amount']     = number_format($orderInfo->order_amount + $postArray['order_edit_amount'], 2, '.', '');
                    $orderAmountLog['order_original_amount'] = $orderInfo->order_amount;
                    $orderAmountLog['order_edit_amount_type']= $postArray['order_edit_amount_type'];
                    $orderAmountLog['order_edit_number']     = $postArray['order_edit_amount'];
                    $orderAmountLog['order_edit_amount_user']= $this->getServiceLocator()->get('adminHelper')->returnAuth('admin_name');
                    $orderAmountLog['order_edit_amount_info']= $postArray['order_edit_amount_info'];
                    $orderAmountLog['order_id']              = $orderId;

                    if($this->getDbshopTable('OrderAmountLogTable')->addOrderAmountLog($orderAmountLog)) {
                        if($this->getDbshopTable('OrderTable')->updateOrder(array('order_amount'=>$orderAmountLog['order_edit_amount']), array('order_id'=>$orderId))) {
                            //事件驱动
                            $this->getEventManager()->trigger('order.changeAmount.backstage.post', $this, array('values'=>$orderAmountLog));
                            $state = 'true';
                        }
                    }
                }
            }
        }
        exit($state);
    }
    /**
     * 获取需要导出的发货单信息
     * @param array $exportArray
     * @return array
     */
    private function getExportShipArray(array $exportArray)
    {
        $shipArray = array();
        $whereArray= array();

        //发货状态
        $shipState = array(
            'all_ship'=> '(o.order_state >= 20 or o.pay_code="hdfk")',//全部发货单
            'no_ship' => '(o.order_state >= 20 or o.pay_code="hdfk") and o.order_state < 40',//未发货的发货单
            'yes_ship'=> 'o.order_state = 40'//已发货，但未收货
        );
        $whereArray[] = $shipState[$exportArray['order_ship_state']];

        //开始时间与结束时间
        $whereArray[] = (isset($exportArray['export_start_time']) and !empty($exportArray['export_start_time'])) ? 'o.order_time >= ' . strtotime($exportArray['export_start_time']) : '';
        $whereArray[] = (isset($exportArray['export_end_time'])   and !empty($exportArray['export_end_time']))   ? 'o.order_time <= ' . strtotime($exportArray['export_end_time'])   : '';

        //配送方式
        $whereArray[] = (isset($exportArray['express_id']) and !empty($exportArray['express_id'])) ? 'o.express_id=' . $exportArray['express_id'] : '';

        //去除数组中的空值
        $whereArray = array_filter($whereArray);

        $shipArray = $this->getDbshopTable('OrderDeliveryAddressTable')->listExportDeliveryaddressArray($whereArray);

        return $shipArray;
    }
    /**
     * 导出发货单的Excel形式
     * @param array $shipArray
     * @param array $exportArray
     */
    private function exportShipExcel(array $shipArray, array $exportArray)
    {
        if(empty($shipArray)) return ;

        require_once DBSHOP_PATH . '/module/Upload/src/Upload/Plugin/xlsxwriter/xlsxwriter.class.php';

        $filename = "发货单_".date("Y-m-d").".xlsx";
        header('Content-disposition: attachment; filename="'.\XLSXWriter::sanitize_filename($filename).'"');
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');

        $writer = new \XLSXWriter();
        $writer->setAuthor('DBShop');

        $languageArray = array(
            'express_name'  => $this->getDbshopLang()->translate('快递信息'),
            'express_fee'   => $this->getDbshopLang()->translate('配送费用'),
            'express_number'=> $this->getDbshopLang()->translate('快递单号'),
        );
        $titleArray = array(
            $this->getDbshopLang()->translate('收货人'),
            $this->getDbshopLang()->translate('收货地址'),
            $this->getDbshopLang()->translate('联系方式'),
            $this->getDbshopLang()->translate('邮政编码'),
            $this->getDbshopLang()->translate('送货时间'),
            $this->getDbshopLang()->translate('付款方式'),
            $this->getDbshopLang()->translate('应收货款(包括运费)')
        );
        $widthsArray = array(25, 40, 30, 25, 25, 25, 25);

        $selectExportState = false;//是否有可选值
        $array = array();//用于获取可导出属性对应的大写字母
        $goodsExport = array();
        if(isset($exportArray['select_export_value']) and !empty($exportArray['select_export_value'])) {
            foreach ($exportArray['select_export_value'] as $selectKey => $selectValue) {
                $array[$selectValue] = $selectValue;
                if(in_array($selectValue, array('goods_name', 'goods_item', 'goods_extend_info', 'buy_num'))) {//用户下面的商品信息
                    $goodsExport[] = $selectValue;
                    continue;
                }
                $titleArray[]   = $languageArray[$selectValue];
                $widthsArray[]  = 30;
            }

            if(!empty($goodsExport)) {
                $titleArray[] = $this->getDbshopLang()->translate('商品信息');
                $widthsArray[]  = 70;
            }

            $selectExportState = true;
        }

        $writer->writeSheetHeader(
            'Sheet1',
            array('string'),
            array('suppress_row'=>true, 'widths' => $widthsArray)
        );

        $writer->writeSheetRow('Sheet1',
            $titleArray,
            array('fill'=>'#eee', 'halign'=>'center', 'border'=>'left,right,top,bottom', 'border-style' => 'thin')
        );

        foreach($shipArray as $shipValue) {
            $exportArray = array();

            $telStr = '';
            if(!empty($shipValue['tel_phone'])) $telStr .= $this->getDbshopLang()->translate('座机').':'.$shipValue['tel_phone'];
            if(!empty($shipValue['mod_phone'])) $telStr .= ($telStr != '' ? "\r\n" : '') . $this->getDbshopLang()->translate('手机').':'.$shipValue['mod_phone'];

            $address = '';
            $address = $shipValue['region_address'].', ';
            $regionArray = @explode(' ', $shipValue['region_info']);
            $regionArray = array_reverse($regionArray);
            $address .= implode(', ', $regionArray);

            $exportArray[] = $shipValue['delivery_name'];
            $exportArray[] = $address;
            $exportArray[] = $telStr;
            $exportArray[] = $shipValue['zip_code'];
            $exportArray[] = $shipValue['express_time_info'];
            $exportArray[] = $shipValue['pay_name'].'['.(!empty($shipValue['pay_time']) ? $this->getDbshopLang()->translate('已付款') : $this->getDbshopLang()->translate('未付款')).']';
            $exportArray[] = $shipValue['currency_symbol'].$shipValue['order_amount'];

            if($selectExportState) {
                if(isset($array['express_name']))   $exportArray[] = $shipValue['express_name'];
                if(isset($array['express_fee']))    $exportArray[] = $shipValue['express_fee'];
                if(isset($array['express_number'])) $exportArray[] = $shipValue['express_number'];
            }

            //商品信息获取
            if(!empty($goodsExport)) {
                $goodsArray = array();
                $goodsArray = $this->getDbshopTable('OrderGoodsTable')->listOrderGoods(array('order_id' => $shipValue['order_id']));
                if (is_array($goodsArray) and !empty($goodsArray)) {
                    $goodsStr = '';
                    foreach($goodsArray as $goodsValue) {
                        if(isset($array['goods_name'])) $goodsStr .= $goodsValue['goods_name'];
                        if(isset($array['goods_item'])) $goodsStr .= '('.$goodsValue['goods_item'].')';
                        if(isset($array['goods_extend_info'])) $goodsStr .= '['.strip_tags($goodsValue['goods_extend_info']).']';
                        if(isset($array['buy_num'])) $goodsStr .= '×'.$goodsValue['buy_num'];
                        $goodsStr .= "\r\n";
                    }
                }
                $exportArray[] = $goodsStr;
            }

            $writer->writeSheetRow('Sheet1',
                $exportArray,
                array('halign'=>'center', 'border'=>'left,right,top,bottom', 'border-style' => 'thin')
            );
        }
        $writer->writeToStdOut();
        exit();

    }
    /**
     * 数据表调用
     * @param string $tableName
     * @return multitype:
     */
    private function getDbshopTable ($tableName='OrderTable')
    {
        if (empty($this->dbTables[$tableName])) {
            $this->dbTables[$tableName] = $this->getServiceLocator()->get($tableName);
        }
        return $this->dbTables[$tableName];
    }
}
