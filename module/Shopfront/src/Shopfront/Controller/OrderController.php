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

use Zend\View\Model\ViewModel;

class OrderController extends FronthomeController
{
    private $dbTables = array();
    private $translator;
    
    public function indexAction ()
    {
        $view  = new ViewModel();
        $view->setTemplate('/shopfront/home/orderlist.phtml');
        //顶部title使用
        $this->layout()->title_name = $this->getDbshopLang()->translate('我的订单');
        
        $array = array();
        
        //获取商品列表 商品分页
        $orderState   = (int) $this->params('order_state', 10);
        $array['order_state'] = $orderState;

        $userId = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');

        $searchArray  = array('buyer_id'=>$userId, 'search_order_sn'=>trim($this->request->getQuery('search_order_sn')));

        if($orderState == -40) {//-40 是退货的查询
            $searchArray['refund_state'] = 1;
        } else {
            $searchArray['order_state'] = $orderState;
            $searchArray['refund_state']= '0';
        }

        $page 		  = $this->params('page',1);
        $array['page']= $page;
        $array['order_list'] = $this->getDbshopTable('OrderTable')->orderPageList(array('page'=>$page, 'page_num'=>16), $searchArray);
        
        //订单状态数量
        $array['order_state_num'] = $this->getDbshopTable('OrderTable')->allStateNumOrder($userId);

        $array['user_info'] = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$userId));


        $view->setVariables($array);
        return $view;
    }
    /**
     * 虚拟商品列表
     * @return ViewModel
     */
    public function virtualGoodsAction()
    {
        $view  = new ViewModel();
        $view->setTemplate('/shopfront/home/virtual-goods.phtml');
        //顶部title使用
        $this->layout()->title_name = $this->getDbshopLang()->translate('虚拟商品');

        $array = array();

        $page 		  = $this->params('page',1);
        $array['page']= $page;
        //订单商品
        $array['virtual_goods'] = $this->getDbshopTable('OrderGoodsTable')->pageListOrderGoods(array('page'=>$page, 'page_num'=>16), array('dbshop_order_goods.buyer_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id'), 'dbshop_order_goods.goods_type'=>2));

        $view->setVariables($array);
        return $view;
    }
    /**
     * 如果已经付款，获取虚拟商品信息
     */
    public function VirtualGoodsInfoAction()
    {
        $array = array('state'=>'false', 'message'=>$this->getDbshopLang()->translate('获取虚拟商品信息失败！'));
        if($this->request->isPost()) {
            $orderId = (int) $this->request->getPost('order_id');
            $goodsId = (int) $this->request->getPost('goods_id');
            $where   = array();
            $where['order_id'] = $orderId;
            $where['goods_id'] = $goodsId;
            $where['virtual_goods_state'] = 2;
            $where['user_id']  = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');
            $virtualGoodsList = $this->getDbshopTable('VirtualGoodsTable')->listVirtualGoods($where);
            if(is_array($virtualGoodsList) and !empty($virtualGoodsList)) exit(json_encode(array('state'=>'true', 'goods'=>$virtualGoodsList)));
        }
        exit(json_encode($array));
    }
    /** 
     * 订单查看
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|\Zend\View\Model\ViewModel
     */
    public function showorderAction ()
    {
        $view  = new ViewModel();
        $view->setTemplate('/shopfront/home/showorder.phtml');
        //顶部title使用
        $this->layout()->title_name = $this->getDbshopLang()->translate('订单详情');

        $array = array();
        $array['page'] = $this->params('page');
        $array['order_state']   = (int) $this->params('order_state', 10);
        //获取订单信息
        $orderId = (int) $this->params('order_id');
        $array['order_info'] = $this->getDbshopTable('OrderTable')->infoOrder(array('order_id'=>$orderId, 'buyer_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')));
        if(!$array['order_info']) return $this->redirect()->toRoute('frontorder/default/order_page', array('action'=>'index', 'page'=>$array['page']));
        
        //订单配送信息
        $array['delivery_address'] = $this->getDbshopTable('OrderDeliveryAddressTable')->infoDeliveryAddress(array('order_id'=>$orderId));
        
        //订单商品
        $array['order_goods'] = $this->getDbshopTable('OrderGoodsTable')->listOrderGoods(array('order_id'=>$orderId));

        //退货信息
        if($array['order_info']->refund_state == 1) {
            $array['refund_order']=$this->getDbshopTable('OrderRefundTable')->infoOrderRefund(array('order_sn'=>$array['order_info']->order_sn));
        }

        //操作历史，主要显示取消说明
        $array['order_log'] = $this->getDbshopTable('OrderLogTable')->listOrderLog(array('order_id'=>$orderId, 'order_state'=>0));

        //订单总价修改历史
        $array['order_amount_log'] = $this->getDbshopTable('OrderAmountLogTable')->listOrderAmountLog(array('order_id'=>$orderId));

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
        
        $view->setVariables($array);
        return $view;
    }
    /**
     * 确认收货
     * @return \Zend\View\Model\ViewModel
     */
    public function orderReceiptAction()
    {
        $view  = new ViewModel();
        $view->setTemplate('/shopfront/home/receiptorder.phtml');
        //顶部title使用
        $this->layout()->title_name = $this->getDbshopLang()->translate('确认收货');

        $array = array();
        $array['page'] = $this->params('page');
        $array['order_state']   = (int) $this->params('order_state', 10);
        
        //当是支付宝支付并且是担保支付情况时，链接直接跳转到支付宝进行操作
        
        //获取订单信息
        $orderId = (int) $this->params('order_id');
        $array['order_info'] = $this->getDbshopTable('OrderTable')->infoOrder(array('order_id'=>$orderId, 'buyer_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')));
        if(!$array['order_info']) return $this->redirect()->toRoute('frontorder/default/order_page', array('action'=>'index', 'order_state'=>$array['order_info']->order_state, 'page'=>$array['page']));
        
        //当为支付宝支付，并且为担保支付时，不能在这里进行确认收货操作
        if($array['order_info']->pay_code == 'alipay' and $array['order_info']->ot_order_state == 25) return $this->redirect()->toRoute('frontorder/default/order_page', array('action'=>'index', 'order_state'=>$array['order_info']->order_state, 'page'=>$array['page']));
        
        //确认收货操作
        if($this->request->isPost()) {
            $postArray = $this->request->getPost()->toArray();
            if($postArray['order_finish'] == 'true'and !empty($array['order_info']->pay_time) and $array['order_info']->order_state == 40) {
                $finishTime = time();
                $this->getDbshopTable('OrderTable')->updateOrder(array('order_state'=>60, 'finish_time'=>$finishTime), array('order_id'=>$orderId));
                //事件驱动
                $this->getEventManager()->trigger('order.finish.front.post', $this, array('values'=>$orderId));
                //保存订单历史
                $this->getDbshopTable('OrderLogTable')->addOrderLog(array('order_id'=>$orderId, 'order_state'=>60, 'state_info'=>$this->getDbshopLang()->translate('确认收货'), 'log_time'=>$finishTime, 'log_user'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_name')));
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
                            //等级积分变更后，判断等级是否有变化
                            $userInfo = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$array['order_info']->buyer_id));
                            $groupId = $this->getDbshopTable('UserGroupTable')->checkUserGroup(array('group_id'=>$userInfo->group_id, 'integral_num'=>$userInfo->integral_type_2_num));
                            if($groupId) {
                                $this->getDbshopTable('UserTable')->updateUser(array('group_id'=>$groupId), array('user_id'=>$userInfo->user_id));
                                $userGroup = $this->getDbshopTable('UserGroupExtendTable')->infoUserGroupExtend(array('group_id'=>$groupId,'language'=>$this->getDbshopLang()->getLocale()));
                                $this->getServiceLocator()->get('frontHelper')->setUserSession(array('group_id'=>$groupId));
                                $this->getServiceLocator()->get('frontHelper')->setUserSession(array('user_group_name'=>$userGroup->group_name));
                            }
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
                $sendArray['subject']     = $this->getDbshopLang()->translate('确认收货交易完成');
                $this->changeStateSendMail($sendArray);
                /*----------------------提醒信息发送----------------------*/

                /*----------------------手机提醒信息发送----------------------*/
                $smsData = array(
                    'buyname'   => $sendArray['buyer_name'],
                    'ordersn'    => $sendArray['order_sn'],
                    'ordertotal' => $sendArray['order_total'],
                    'expressname'=> $sendArray['express_name'],
                    'expressnumber' => $sendArray['express_number'],
                    'time'    => $sendArray['time'],
                );
                try {
                    $this->getServiceLocator()->get('shop_send_sms')->toSendSms(
                        $smsData,
                        $this->getServiceLocator()->get('frontHelper')->getUserSession('user_phone'),
                        'alidayu_finish_order_template_id',
                        $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')
                    );
                } catch(\Exception $e) {

                }
                /*----------------------手机提醒信息发送----------------------*/
                
                return $this->redirect()->toRoute('frontorder/default/order_page', array('action'=>'index','order_state'=>60, 'page'=>$array['page']));
            }
            return $this->redirect()->toRoute('frontorder/default/order_page', array('action'=>'index','order_state'=>$array['order_info']->order_state, 'page'=>$array['page']));
        }
            
        //订单商品
        $array['order_goods'] = $this->getDbshopTable('OrderGoodsTable')->listOrderGoods(array('order_id'=>$orderId));
        //订单总价修改历史
        $array['order_amount_log'] = $this->getDbshopTable('OrderAmountLogTable')->listOrderAmountLog(array('order_id'=>$orderId));
        //订单配送信息
        $array['delivery_address'] = $this->getDbshopTable('OrderDeliveryAddressTable')->infoDeliveryAddress(array('order_id'=>$orderId));
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
        
        $view->setVariables($array);
        return $view;
    }
    /** 
     * 订单状态改变
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function changestateAction ()
    {
        $orderId      = (int) $this->params('order_id');
        $orderState   = (int) $this->params('order_state');
       
        $orderInfo    = $this->getDbshopTable('OrderTable')->infoOrder(array('order_id'=>$orderId, 'buyer_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')));
        
        if(($orderState == 0 and ($orderInfo->order_state == 10 or ($orderInfo->order_state == 30 and $orderInfo->pay_code == 'hdfk'))) and !empty($orderInfo)) {//取消订单处理
            $this->getDbshopTable('OrderTable')->updateOrder(array('order_state'=>$orderState), array('order_id'=>$orderId));
            $this->returnGoodsStock($orderId);//取消订单时的库存处理
            //加入状态记录
            $this->getDbshopTable('OrderLogTable')->addOrderLog(array('order_id'=>$orderId, 'order_state'=>'0', 'state_info'=>$this->getDbshopLang()->translate('买家自行取消'), 'log_time'=>time(), 'log_user'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_name')));
            //检查是否有消费积分付款
            if($orderInfo->integral_buy_num > 0) {
                $integralLogArray = array();
                $integralLogArray['user_id']           = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');
                $integralLogArray['user_name']         = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_name');
                $integralLogArray['integral_log_info'] = $this->getDbshopLang()->translate('取消订单，订单号为：') . $orderInfo->order_sn;
                $integralLogArray['integral_num_log']  = $orderInfo->integral_buy_num;
                $integralLogArray['integral_log_time'] = time();
                if($this->getDbshopTable('IntegralLogTable')->addIntegralLog($integralLogArray)) {
                    //会员消费积分更新
                    $this->getDbshopTable('UserTable')->updateUserIntegralNum($integralLogArray, array('user_id'=>$integralLogArray['user_id']));
                }
            }
            /*----------------------提醒信息发送----------------------*/
            $sendArray['buyer_name']  = $orderInfo->buyer_name;
            $sendArray['order_sn']    = $orderInfo->order_sn;
            $sendArray['order_total'] = $orderInfo->order_amount;
            $sendArray['time']        = time();
            $sendArray['buyer_email'] = $orderInfo->buyer_email;
            $sendArray['order_state'] = 'cancel_order';
            $sendArray['time_type']   = 'canceltime';
            $sendArray['subject']     = $this->getDbshopLang()->translate('订单取消');
            $this->changeStateSendMail($sendArray);
            /*----------------------提醒信息发送----------------------*/

            /*----------------------手机提醒信息发送----------------------*/
            $smsData = array(
                'buyname'   => $sendArray['buyer_name'],
                'ordersn'   => $sendArray['order_sn'],
                'ordertotal'=> $sendArray['order_total'],
                'time'    => $sendArray['time'],
            );
            try {
                $this->getServiceLocator()->get('shop_send_sms')->toSendSms(
                    $smsData,
                    $this->getServiceLocator()->get('frontHelper')->getUserSession('user_phone'),
                    'alidayu_cancel_order_template_id',
                    $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')
                );
            } catch(\Exception $e) {

            }
            /*----------------------手机提醒信息发送----------------------*/

            $this->getEventManager()->trigger('order.cancel.front.post', $this, array('values'=>$orderId));
        }
        return $this->redirect()->toRoute('frontorder/default/order_page', array('action'=>'index','order_state'=>$orderInfo->order_state, 'page'=>$this->params('page')));
    }
    /** 
     * 取消订单时的库存处理
     * @param unknown $orderId
     */
    private function returnGoodsStock ($orderId)
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
     * 订单删除
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function delOrderAction ()
    {
        $orderId  = (int) $this->params('order_id');
        
        $orderInfo    = $this->getDbshopTable('OrderTable')->infoOrder(array('order_id'=>$orderId, 'buyer_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')));
        if($orderInfo && $orderInfo->order_state == 0) {
            $this->getDbshopTable('OrderTable')->delOrder(array('order_id'=>$orderId));
            //事件驱动
            $this->getEventManager()->trigger('order.del.front.post', $this, array('values'=>$orderInfo));
        }
        
        return $this->redirect()->toRoute('frontorder/default/order_page', array('action'=>'index','order_state'=>$orderInfo->order_state, 'page'=>$this->params('page')));
    }
    /** 
     * 商品评价提交
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|\Zend\View\Model\ViewModel
     */
    public function goodscommentAction ()
    {
        $view           = new ViewModel();
        $view->setTemplate('/shopfront/home/goodscomment.phtml');
        
        $array          = array();
        
        $array['page']  = (int) $this->params('page', 1);
        $array['order_state'] = $this->params('order_state');

        //订单商品信息
        $orderGoodsId   = (int) $this->params('order_goods_id');
        $orderGoodsInfo = $this->getDbshopTable('OrderGoodsTable')->InfoOrderGoods(array('order_goods_id'=>$orderGoodsId, 'buyer_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')));
        if(!$orderGoodsInfo) return $this->redirect()->toRoute('frontorder/default/order_page', array('action'=>'index','order_state'=>$this->params('order_state'), 'page'=>$this->params('page')));
        $array['goods_info'] = $orderGoodsInfo;
        
        //订单信息
        $array['order_info'] = $this->getDbshopTable('OrderTable')->infoOrder(array('order_id'=>$orderGoodsInfo->order_id, 'buyer_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')));
        
        //评价保存
        if($this->request->isPost() && $array['goods_info'] && $array['goods_info']->comment_state != 1) {
            $commentArray = $this->request->getPost()->toArray();
            $commentArray['comment_writer'] = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_name');
            $commentArray['comment_time']   = time();
            $commentArray['goods_id']       = $array['goods_info']->goods_id;
            $commentArray['order_goods_id'] = $array['goods_info']->order_goods_id;
            
            if($this->getDbshopTable('GoodsCommentTable')->addGoodsComment($commentArray)) {
                $listCommentArray = $this->getDbshopTable('GoodsCommentTable')->allGoodsComment(array('goods_id'=>$array['goods_info']->goods_id));
                if(is_array($listCommentArray) and !empty($listCommentArray)) $commentCount = count($listCommentArray); else $commentCount = 1;
                
                //添加订单商品基础评价
                $baseCommentArray = array();
                $baseCommentArray['goods_id']       = $array['goods_info']->goods_id;
                $baseCommentArray['comment_writer'] = $commentArray['comment_writer'];
                $baseCommentArray['comment_time']   = $commentArray['comment_time'];
                $baseCommentArray['comment_count']  = $commentCount;
                $baseCommentInfo  = $this->getDbshopTable('GoodsCommentBaseTable')->InfoGoodsCommentBase(array('goods_id'=>$array['goods_info']->goods_id));
                if($baseCommentInfo) {
                    $this->getDbshopTable('GoodsCommentBaseTable')->updataGoodsCommentBase($baseCommentArray, array('goods_id'=>$array['goods_info']->goods_id));
                } else {
                    $this->getDbshopTable('GoodsCommentBaseTable')->addGoodsCommentBase($baseCommentArray);
                }
                //更新订单商品评价状态
                $this->getDbshopTable('OrderGoodsTable')->updateOrderGoods(array('comment_state'=>1), array('order_goods_id'=>$orderGoodsId, 'buyer_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')));
                //更新订单表中的序列化商品评价状态
                $orderGoodsSerArray = unserialize($array['order_info']->goods_serialize);
                $orderGoodsSerArray[$orderGoodsId]['comment_state'] = 1;
                $this->getDbshopTable('OrderTable')->updateOrder(array('goods_serialize'=>serialize($orderGoodsSerArray)), array('order_id'=>$orderGoodsInfo->order_id));
                
                return $this->redirect()->toRoute('frontorder/default/order_page', array('action'=>'index','order_state'=>$array['order_state'], 'page'=>$array['page']));
            }
        }
        
        //已评价信息
        if($array['goods_info']->comment_state) {
            $array['goodsCommentInfo'] = $this->getDbshopTable('GoodsCommentTable')->infoGoodsComment(array('order_goods_id'=>$orderGoodsId, 'comment_writer'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_name')));
        }
        
        $view->setVariables($array);
        return $view;
    }
    /** 
     * 订单支付
     */
    public function orderpayAction ()
    {
        $orderId    = (int) $this->params('order_id');
        //订单基本信息
        $orderInfo  = $this->getDbshopTable('OrderTable')->infoOrder(array('order_id'=>$orderId));
        if($orderInfo->order_state <= 0 or $orderInfo->pay_code == '' or $orderInfo->buyer_id != $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')) { @header("Location: " . $this->getRequest()->getServer('HTTP_REFERER')); exit(); }

        if($this->request->isPost()) {
            $postArray = $this->request->getPost()->toArray();
            if(!empty($postArray['payment_code']) and $postArray['payment_code'] != $orderInfo->pay_code) {
                $paymentArray = array();
                $postArray['payment_code'] = basename($postArray['payment_code']);
                //获取支付方式信息
                if(file_exists(DBSHOP_PATH . '/data/moduledata/Payment/' . $postArray['payment_code'] . '.php')) {
                    $paymentArray   = include(DBSHOP_PATH . '/data/moduledata/Payment/' . $postArray['payment_code'] . '.php');
                    $payName        = $paymentArray['payment_name']['content'];

                    $paymentFee = ((strpos($paymentArray['payment_fee']['content'], '%') !== false) ? round($orderInfo->goods_amount * str_replace('%', '', $paymentArray['payment_fee']['content'])/100, 2) : round($this->getServiceLocator()->get('frontHelper')->currencyPrice($paymentArray['payment_fee']['content'], $orderInfo->currency), 2));

                    $orderAmount = $orderInfo->order_amount - $orderInfo->pay_fee + $paymentFee;
                    if($this->getDbshopTable('OrderTable')->updateOrder(array('order_amount'=>$orderAmount, 'pay_fee'=>$paymentFee, 'pay_code'=>$postArray['payment_code'], 'pay_name'=>$payName), array('order_id'=>$orderId))) {
                        $orderInfo->pay_code = $postArray['payment_code'];
                    }
                }
            }
        }

        //订单配送信息
        $deliveryAddress = $this->getDbshopTable('OrderDeliveryAddressTable')->infoDeliveryAddress(array('order_id'=>$orderId));
        //订单商品
        $orderGoods = $this->getDbshopTable('OrderGoodsTable')->listOrderGoods(array('order_id'=>$orderId));
        //打包数据，传给下面的支付输出
		$httpHost = $this->getServiceLocator()->get('frontHelper')->dbshopHttpHost();
		$httpType = $this->getServiceLocator()->get('frontHelper')->dbshopHttpOrHttps();
        $paymentData = array(
            'shop_name' => $this->getServiceLocator()->get('frontHelper')->websiteInfo('shop_name'),
            'order'     => $orderInfo,
            'address'   => $deliveryAddress,
            'goods'     => $orderGoods,
            'return_url'=> $httpType . $httpHost . $this->url()->fromRoute('frontorder/default/order_id', array('action'=>'orderReturnPay', 'order_id'=>$orderId)),
            'notify_url'=> $httpType . $httpHost . $this->url()->fromRoute('frontorder/default/order_id', array('action'=>'orderNotifyPay', 'order_id'=>$orderId)),
            'cancel_url'=> $httpType . $httpHost . $this->url()->fromRoute('frontorder/default'),
            'order_url' => $httpType . $httpHost . $this->url()->fromRoute('frontorder/default/order_id', array('action'=>'showorder', 'order_id'=>$orderId)),
        );
        $result = $this->getServiceLocator()->get('payment')->payServiceSet($orderInfo->pay_code)->paymentTo($paymentData);

        if($orderInfo->pay_code == 'wxpay') {//微信扫码支付页面
            $view           = new ViewModel();
            $view->setTemplate('/shopfront/home/orderpay.phtml');
            //顶部title使用
            $this->layout()->title_name = $this->getDbshopLang()->translate('微信扫码支付');

            $view->setVariables(array('result' => $result, 'orderinfo'=> $orderInfo, 'qrcode_url' => $httpType . $httpHost . $this->url()->fromRoute('frontorder/default', array('action'=>'orderQrcode')).'?data='.$result['code_url']));
            return $view;

        } else exit();
    }
    /**
     * 支付返回验证操作
     */
    public function orderReturnPayAction ()
    {
        $view           = new ViewModel();
        $view->setTemplate('/shopfront/home/order-return-pay.phtml');

        $orderId = (int) $this->params('order_id');
        //订单基本信息
        $orderInfo  = $this->getDbshopTable('OrderTable')->infoOrder(array('order_id'=>$orderId));
        //判断支付方式是否非空，或者是否与购买者相对应
        if($orderInfo->pay_code == '' or $orderInfo->order_state >= 20 or $orderInfo->buyer_id != $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')) return $this->redirect()->toRoute('frontorder/default/order_state', array('action'=>'index', 'order_state'=>20));
        //当是线下付款时，如果不进行下面的判断处理，会出现问题
        if($orderInfo->pay_code == 'xxzf' and $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id') == '') return $this->redirect()->toRoute('frontuser/default',array('action'=>'login'));
        //当时支付方式为余额付款时，进行单独处理，支付状态，抛给orderInfo
        if($orderInfo->pay_code == 'yezf') $orderInfo = $this->userMoneyPayOper($orderInfo);
        //语言包及支付处理，在支付模块中进行
        $language      = $this->paymentLanguage($orderInfo);

        $array = $this->getServiceLocator()->get('payment')->payServiceSet($orderInfo->pay_code)->paymentReturn($orderInfo, $language);
        //付款成功
        if(isset($array['payFinish']) and $array['payFinish']) {
            $paymentFinishTime = time();
            //在线支付或者为线下支付时的支付证明内容,当为线下支付付款时，这里状态修改为付款中状态（15）
            $updateOrderArray = array('order_state'=>($orderInfo->pay_code == 'xxzf' ? 15 : 20), 'order_out_sn'=>(isset($_REQUEST['trade_no']) ? $_REQUEST['trade_no'] : ''), 'pay_time'=>$paymentFinishTime, 'pay_certification'=>(isset($array['state_info']) ? $array['state_info'] : ''));
            //当为支付宝支付并且为担保支付时，otState 为 25 即为担保已经支付，但还没有到卖家手中
            if($orderInfo->pay_code == 'alipay' and isset($array['otState']) and $array['otState'] == 25) {
                $updateOrderArray['ot_order_state'] = 25;
            }
            //只有在订单状态非付款完成和非付款中时，才进行此处理
            if($orderInfo->order_state != 20 and $orderInfo->order_state != 15) {
                $this->getDbshopTable('OrderTable')->updateOrder($updateOrderArray, array('order_id'=>$orderId));
                //保存订单历史
                $this->getDbshopTable('OrderLogTable')->addOrderLog(array('order_id'=>$orderId, 'order_state'=>$updateOrderArray['order_state'], 'state_info'=>((isset($array['state_info']) and !empty($array['state_info'])) ? $array['state_info'] : ($updateOrderArray['order_state'] == 15 ? $this->getDbshopLang()->translate('付款进行中') : $this->getDbshopLang()->translate('支付完成'))), 'log_time'=>$paymentFinishTime, 'log_user'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_name')));
            }

            if($updateOrderArray['order_state'] == 20 and $orderInfo->order_state != 20) {//当付款状态为完成时(如果)，进行邮件发送，如果只是付款中，则不进行邮件发送

                /*----------------------提醒信息发送----------------------*/
                $sendArray['buyer_name']  = $orderInfo->buyer_name;
                $sendArray['order_sn']    = $orderInfo->order_sn;
                $sendArray['order_total'] = $orderInfo->order_amount;
                $sendArray['time']        = $paymentFinishTime;
                $sendArray['buyer_email'] = $orderInfo->buyer_email;
                $sendArray['order_state'] = 'payment_finish';
                $sendArray['time_type']   = 'paymenttime';
                $sendArray['subject']     = $this->getDbshopLang()->translate('订单付款完成');
                $this->changeStateSendMail($sendArray);
                /*----------------------提醒信息发送----------------------*/

                /*----------------------手机提醒信息发送----------------------*/
                $smsData = array(
                    'buyname'   => $sendArray['buyer_name'],
                    'ordersn'   => $sendArray['order_sn'],
                    'ordertotal'=> $sendArray['order_total'],
                    'time'    => $sendArray['time'],
                );
                try {
                    $this->getServiceLocator()->get('shop_send_sms')->toSendSms(
                        $smsData,
                        $this->getServiceLocator()->get('frontHelper')->getUserSession('user_phone'),
                        'alidayu_payment_order_template_id',
                        $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')
                    );
                } catch(\Exception $e) {

                }
                /*----------------------手机提醒信息发送----------------------*/
                //事件驱动
                $this->getEventManager()->trigger('order.pay.front.post', $this, array('values'=>$orderId));
            }
        } elseif (isset($array['payFinish']) and !$array['payFinish']) {//未成功，可能在进行中
            
            //if ($orderInfo->pay_code == 'alipay') $array = $this->alipayPayOper($array, $orderInfo);
        }
        $view->setVariables($array);
        return $view;
    }
    /**
     * 支付通知接收
     */
    public function orderNotifyPayAction ()
    {
        $orderId = (int) $this->params('order_id');
        //订单基本信息
        $orderInfo  = $this->getDbshopTable('OrderTable')->infoOrder(array('order_id'=>$orderId));
        if($orderInfo->pay_code == '') exit();
        
        //语言包及支付处理，在支付模块中进行
        $language      = $this->paymentLanguage($orderInfo);
        $array = $this->getServiceLocator()->get('payment')->payServiceSet($orderInfo->pay_code)->paymentNotify($orderInfo, $language);

        //付款成功
        if(isset($array['payFinish']) and $array['payFinish']) {
            $updateOrderArray = array('order_state'=>20, 'order_out_sn'=>(isset($_REQUEST['trade_no']) ? $_REQUEST['trade_no'] : ''), 'pay_time'=>time());
            //当为支付宝支付并且为担保支付时，otState 为 25 即为担保已经支付，但还没有到卖家手中
            if($orderInfo->pay_code == 'alipay' and isset($array['otState']) and $array['otState'] == 25) {
                $updateOrderArray['ot_order_state'] = 25;
            }

            if($updateOrderArray['order_state'] == 20 and $orderInfo->order_state != 20) {

                $this->getDbshopTable('OrderTable')->updateOrder($updateOrderArray, array('order_id'=>$orderId));
                //保存订单历史
                $this->getDbshopTable('OrderLogTable')->addOrderLog(array('order_id'=>$orderId, 'order_state'=>20, 'state_info'=>$this->getDbshopLang()->translate('支付完成'), 'log_time'=>time(), 'log_user'=>$orderInfo->buyer_name));

                /*----------------------提醒信息发送----------------------*/
                $sendArray['buyer_name']  = $orderInfo->buyer_name;
                $sendArray['order_sn']    = $orderInfo->order_sn;
                $sendArray['order_total'] = $orderInfo->order_amount;
                $sendArray['time']        = time();
                $sendArray['buyer_email'] = $orderInfo->buyer_email;
                $sendArray['order_state'] = 'payment_finish';
                $sendArray['time_type']   = 'paymenttime';
                $sendArray['subject']     = $this->getDbshopLang()->translate('订单付款完成');
                $this->changeStateSendMail($sendArray);
                /*----------------------提醒信息发送----------------------*/

                /*----------------------手机提醒信息发送----------------------*/
                $smsData = array(
                    'buyname'   => $sendArray['buyer_name'],
                    'ordersn'   => $sendArray['order_sn'],
                    'ordertotal'=> $sendArray['order_total'],
                    'time'    => $sendArray['time'],
                );
                try {
                    $userInfo = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$orderInfo->buyer_id));
                    $this->getServiceLocator()->get('shop_send_sms')->toSendSms(
                        $smsData,
                        $userInfo->user_phone,
                        'alidayu_payment_order_template_id',
                        $orderInfo->buyer_id
                    );
                } catch(\Exception $e) {

                }
                /*----------------------手机提醒信息发送----------------------*/
                //事件驱动
                $this->getEventManager()->trigger('order.pay.front.post', $this, array('values'=>$orderId));
            }
        } elseif (isset($array['payFinish']) and !$array['payFinish']) {//未成功，可能在进行中
        
            if ($orderInfo->pay_code == 'alipay' or $orderInfo->pay_code == 'malipay') {//对于支付宝发货成功和确认收货处理
                $state = $this->alipayPayOper($array, $orderInfo);
                if(!$state) exit('fail');
            }
        }
        exit($array['message']);
    }
    /**
     * app端支付后的异步通知信息接收地址(只支持 支付宝和手机微信支付)
     */
    public function orderAppNotifyPayAction()
    {
        $orderId = (int) $this->params('order_id');
        //订单基本信息
        $orderInfo  = $this->getDbshopTable('OrderTable')->infoOrder(array('order_id'=>$orderId));
        if($orderInfo->pay_code == '' or !in_array($orderInfo->pay_code, array('alipay', 'malipay', 'wxmpay'))) exit();

        if($orderInfo->order_state >= 20) {
            $stateMessage = array(
                'alipay' => 'SUCCESS',
                'malipay'=> 'SUCCESS',
                'wxmpay' => "<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>"
            );
            exit($stateMessage[$orderInfo->pay_code]);
        }

        $finishMessage = '';
        $finishState   = false;
        if($orderInfo->order_state < 20) {
            require DBSHOP_PATH . '/module/Extendapp/Dbapi/config/app/appPayConf.php';
            //支付宝
            if(in_array($orderInfo->pay_code, array('alipay', 'malipay'))) {
                require DBSHOP_PATH . '/module/Extendapp/Dbapi/src/Dbapi/Pay/AliAppPay/AopSdk.php';
                $aop = new \AopClient();
                $aop->alipayrsaPublicKey = file_get_contents(ALIPAY_PUBLIC_KEY);
                $flag = $aop->rsaCheckV1($_POST, NULL, SIGN_TYPE);
                $out_trade_no = $_REQUEST['out_trade_no']; //订单号
                $total_amount = $_REQUEST['total_amount']; //订单金额
                $trade_status = $_REQUEST['trade_status']; //支付状态
                if(
                    $flag
                    and $out_trade_no == $orderInfo->order_sn
                    and $total_amount == $orderInfo->order_amount
                    and in_array($trade_status, array('TRADE_SUCCESS', 'TRADE_FINISHED'))
                ) {
                    $finishMessage = 'SUCCESS';
                    $finishState   = true;
                    $updateOrderArray = array(
                        'order_state'=>20,
                        'order_out_sn'=>(isset($_REQUEST['trade_no']) ? $_REQUEST['trade_no'] : ''),
                        'pay_time'=>time()
                    );
                }
            }
            //微信支付
            if($orderInfo->pay_code == 'wxmpay') {
                require DBSHOP_PATH . '/module/Extendapp/Dbapi/src/Dbapi/Pay/WxAppPay/lib/Autoload.class.php';
                spl_autoload_register("Autoload::autoload");

                $encpt = \WeEncryption::getInstance();
                $obj = $encpt->getNotifyData();
                if ($obj === false) {
                    exit;
                }

                $objArray = (array) $obj;
                if (is_array($objArray) and !empty($objArray)) {
                    $postSign = $objArray['sign'];//服务器传递过来的签名
                    unset($objArray['sign']);

                    $sign = $encpt->getSign($objArray);//生成本地签名

                    if ($sign == $postSign) {
                        $finishMessage = "<xml>
					<return_code><![CDATA[SUCCESS]]></return_code>
					<return_msg><![CDATA[OK]]></return_msg>
				</xml>";
                        $finishState = true;
                        $updateOrderArray = array(
                            'order_state'=>20,
                            'order_out_sn'=>(isset($objArray['out_trade_no']) ? $objArray['out_trade_no'] : ''),
                            'pay_time'=>time()
                        );
                    }
                }
            }

            if($finishState) {//支付成功，进行其他处理
                $this->getDbshopTable('OrderTable')->updateOrder($updateOrderArray, array('order_id'=>$orderId));
                //保存订单历史
                $this->getDbshopTable('OrderLogTable')->addOrderLog(array('order_id'=>$orderId, 'order_state'=>20, 'state_info'=>$this->getDbshopLang()->translate('支付完成'), 'log_time'=>time(), 'log_user'=>$orderInfo->buyer_name));

                //事件驱动
                $this->getEventManager()->trigger('order.pay.front.post', $this, array('values'=>$orderId));

                /*----------------------提醒信息发送----------------------*/
                $sendArray['buyer_name']  = $orderInfo->buyer_name;
                $sendArray['order_sn']    = $orderInfo->order_sn;
                $sendArray['order_total'] = $orderInfo->order_amount;
                $sendArray['time']        = time();
                $sendArray['buyer_email'] = $orderInfo->buyer_email;
                $sendArray['order_state'] = 'payment_finish';
                $sendArray['time_type']   = 'paymenttime';
                $sendArray['subject']     = $this->getDbshopLang()->translate('订单付款完成');
                $this->changeStateSendMail($sendArray);
                /*----------------------提醒信息发送----------------------*/

                /*----------------------手机提醒信息发送----------------------*/
                $smsData = array(
                    'buyname'   => $sendArray['buyer_name'],
                    'ordersn'   => $sendArray['order_sn'],
                    'ordertotal'=> $sendArray['order_total'],
                    'time'    => $sendArray['time'],
                );
                try {
                    $userInfo = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$orderInfo->buyer_id));
                    $this->getServiceLocator()->get('shop_send_sms')->toSendSms(
                        $smsData,
                        $userInfo->user_phone,
                        'alidayu_payment_order_template_id',
                        $orderInfo->buyer_id
                    );
                } catch(\Exception $e) {

                }
                /*----------------------手机提醒信息发送----------------------*/
            }
        }
        exit($finishMessage);
    }
    /** 
     * 支付付款过程中的处理
     * @param array $data
     */
    private function alipayPayOper(array $data, $orderInfo)
    {
        $stateNum  = $data['stateNum'];
        $typeTime  = '';
        $stateInfo = '';
        $userName  = $orderInfo->buyer_name;
        $timeStr   = time();
        //发货处理
        if($stateNum == 40 and $orderInfo->order_state < 40 and $orderInfo->order_state > 0) {
            $typeTime  = 'express_time';
            $stateInfo = $this->getDbshopLang()->translate('发货完成');
            $userName  = $this->getDbshopLang()->translate('管理员');
        } else if ($stateNum == 60 and $orderInfo->order_state < 60 and $orderInfo->order_state >= 40) {
            $typeTime  = 'finish_time';
            $stateInfo = $this->getDbshopLang()->translate('确认收货');
            /*----------------------提醒信息发送----------------------*/
            //订单配送信息
            $deliveryAddress = $this->getDbshopTable('OrderDeliveryAddressTable')->infoDeliveryAddress(array('order_id'=>$orderInfo->order_id));

            $sendArray['buyer_name']  = $orderInfo->buyer_name;
            $sendArray['order_sn']    = $orderInfo->order_sn;
            $sendArray['order_total'] = $orderInfo->order_amount;
            $sendArray['express_name']  = $deliveryAddress->express_name;
            $sendArray['express_number']= $deliveryAddress->express_number;
            $sendArray['time']        = $timeStr;
            $sendArray['buyer_email'] = $orderInfo->buyer_email;
            $sendArray['order_state'] = 'transaction_finish';
            $sendArray['time_type']   = 'finishtime';
            $sendArray['subject']     = $this->getDbshopLang()->translate('确认收货交易完成');
            $this->changeStateSendMail($sendArray);
            /*----------------------提醒信息发送----------------------*/

            /*----------------------手机提醒信息发送----------------------*/
            $smsData = array(
                'buyname'   => $sendArray['buyer_name'],
                'ordersn'    => $sendArray['order_sn'],
                'ordertotal'   => $sendArray['order_total'],
                'expressname'  => $sendArray['express_name'],
                'expressnumber'=> $sendArray['express_number'],
                'time'    => $sendArray['time'],
            );
            try {
                $this->getServiceLocator()->get('shop_send_sms')->toSendSms(
                    $smsData,
                    $this->getServiceLocator()->get('frontHelper')->getUserSession('user_phone'),
                    'alidayu_finish_order_template_id',
                    $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')
                );
            } catch(\Exception $e) {

            }
            /*----------------------手机提醒信息发送----------------------*/
        } else {
            return false;
        }
        
        $this->getDbshopTable('OrderTable')->updateOrder(array('order_state'=>$stateNum, $typeTime=>$timeStr), array('order_id'=>$orderInfo->order_id));
        $this->getDbshopTable('OrderLogTable')->addOrderLog(array('order_id'=>$orderInfo->order_id, 'order_state'=>$stateNum, 'state_info'=>$stateInfo, 'log_time'=>$timeStr, 'log_user'=>$userName));
        
        return true;
    }

    /**
     * 订单支付二维码（目前用于位置支付）
     */
    public function orderQrcodeAction () {
        require_once DBSHOP_PATH . '/module/Upload/src/Upload/Plugin/Phpqrcode/phpqrcode.php';
        \QRcode::png(urldecode($this->request->getQuery('data')));
        exit;
    }
    /**
     * ajax获取订单付款状态，目前用于微信扫码
     */
    public function ajaxOrderStatusAction() {
        $orderSn = $this->request->getQuery('order_sn');
        $userId = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');
        $jsonOrderStatus = array();
        if(!empty($orderSn) and !empty($userId)) {
            $orderInfo = $this->getDbshopTable('OrderTable')->infoOrder(array('order_sn'=>$orderSn, 'buyer_id'=>$userId));
            if(isset($orderInfo->order_state) and $orderInfo->order_state >= 20) $jsonOrderStatus = array('state'=>'true');
        } else $jsonOrderStatus = array('state'=>'false');

        exit(json_encode($jsonOrderStatus));
    }
    /**
     * 获取订单的支付信息列表
     */
    public function getOrderPayAction()
    {
        $orderId    = (int) $this->request->getPost('order_id');
        $orderInfo  = $this->getDbshopTable('OrderTable')->infoOrder(array('order_id'=>$orderId,'order_state'=>10, 'buyer_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')));
        if(empty($orderInfo)) exit(json_encode(array('state'=>'false', 'message'=>$this->getDbshopLang()->translate('该订单不存在 或者 不符合付款条件，无法进行支付处理！'))));

        /*----------------------支付方式----------------------*/
        $paymentArray = array();
        $filePath      = DBSHOP_PATH . '/data/moduledata/Payment/';
        if(is_dir($filePath)) {
            $dh = opendir($filePath);
            while (false !== ($fileName = readdir($dh))) {
                if($fileName != '.' and $fileName != '..' and stripos($fileName, '.php') !== false and $fileName != '.DS_Store') {
                    $paymentInfo = include($filePath . $fileName);

                    if($paymentInfo['editaction'] == 'hdfk') continue;

                    //判断是否显示在当前平台
                    if(isset($paymentInfo['payment_show']['checked']) and !empty($paymentInfo['payment_show']['checked'])) {
                        $showArray = is_array($paymentInfo['payment_show']['checked']) ? $paymentInfo['payment_show']['checked'] : array($paymentInfo['payment_show']['checked']);
                        if(!in_array('pc', $showArray) and !in_array('all', $showArray) and $paymentInfo['editaction'] != $orderInfo->pay_code) continue;
                    } else continue;

                    //判断是否符合当前的货币要求
                    $currencyState = false;
                    if(isset($paymentInfo['payment_currency']['checked']) and !empty($paymentInfo['payment_currency']['checked'])) {
                        $currencyArray = is_array($paymentInfo['payment_currency']['checked']) ? $paymentInfo['payment_currency']['checked'] : array($paymentInfo['payment_currency']['checked']);
                        $currencyState = in_array($orderInfo->currency, $currencyArray) ? true : false;
                    } elseif (in_array($paymentInfo['editaction'], array('xxzf'))) {//线下支付时，不进行货币判断
                        $currencyState = true;
                    }

                    if($paymentInfo['payment_state']['checked'] == 1 and $currencyState) {
                        $paymentInfo['payment_fee']['content'] = ((strpos($paymentInfo['payment_fee']['content'], '%') !== false) ? round($orderInfo->goods_amount * str_replace('%', '', $paymentInfo['payment_fee']['content'])/100, 2) : round($this->getServiceLocator()->get('frontHelper')->currencyPrice($paymentInfo['payment_fee']['content'], $orderInfo->currency), 2));
                        $paymentArray[] = $paymentInfo;
                    }
                }
            }
        }

        exit(json_encode(array('state'=>'true', 'order'=>(array) $orderInfo, 'payment'=>$paymentArray)));
    }
    /**
     * 在上面支付中需要用到的语言包
     * @param $orderInfo
     * @return array
     */
    private function paymentLanguage($orderInfo)
    {
        $payFinish = $this->getDbshopLang()->translate('支付完成').'&nbsp;<a class="btn btn-primary" href="'.$this->url()->fromRoute('frontorder/default/order_state', array('action'=>'index', 'order_state'=>20)).'"><i class="icon-arrow-left icon-white"></i> '.$this->getDbshopLang()->translate('返回订单').'</a>';
        //如果订单中没有实物商品，则对支付成功返回地址进行更改
        if(isset($orderInfo->order_id) and !empty($orderInfo->order_id)) {
            $vGoodsInfo = $this->getDbshopTable('OrderGoodsTable')->InfoOrderGoods(array('order_id'=>$orderInfo->order_id, 'buyer_id'=>$orderInfo->buyer_id, 'goods_type'=>1));
            if(empty($vGoodsInfo)) $payFinish = $this->getDbshopLang()->translate('支付完成').'&nbsp;<a class="btn btn-primary" href="'.$this->url()->fromRoute('frontorder/default/order_state', array('action'=>'index', 'order_state'=>40)).'"><i class="icon-arrow-left icon-white"></i> '.$this->getDbshopLang()->translate('返回订单').'</a>';
        }

        $array = array(
                'order_total'       =>$this->getDbshopLang()->translate('订单总金额'),
                'goods_item'        =>$this->getDbshopLang()->translate('商品编号'),
                'goods_name'        =>$this->getDbshopLang()->translate('商品名称'),
                'buy_num'           =>$this->getDbshopLang()->translate('购买数量'),
                'goods_price'       =>$this->getDbshopLang()->translate('商品单价'),
                'goods_spec'        =>$this->getDbshopLang()->translate('商品规格'),
                'shipping_fee'      =>$this->getDbshopLang()->translate('配送费用'),
                'submit_pay'        =>$this->getDbshopLang()->translate('确认付款'),
                'xxzf_submit_pay'   =>$this->getDbshopLang()->translate('提交支付凭证'),
                'pay_xxzf_finish'   =>$this->getDbshopLang()->translate('支付凭证提交完成，等待管理员审核！').'&nbsp;<a class="btn btn-primary" href="'.$this->url()->fromRoute('frontorder/default/order_state', array('action'=>'index', 'order_state'=>15)).'"><i class="icon-arrow-left icon-white"></i> '.$this->getDbshopLang()->translate('返回订单').'</a>',
                'pay_finish'        =>$payFinish,
                'pay_yezf_error'    =>$this->getDbshopLang()->translate('余额支付失败，您的账户余额不足，请您充值后再次进行支付。').'&nbsp;<a class="btn btn-primary" href="'.$this->url()->fromRoute('frontorder/default/order_state', array('action'=>'index', 'order_state'=>10)).'"><i class="icon-arrow-left icon-white"></i> '.$this->getDbshopLang()->translate('返回订单').'</a>',
                'pay_currency_error'=>$this->getDbshopLang()->translate('您订单中的付款货币，与系统当前默认货币不匹配，无法进行支付。').'&nbsp;<a class="btn btn-primary" href="'.$this->url()->fromRoute('frontorder/default/order_state', array('action'=>'index', 'order_state'=>10)).'"><i class="icon-arrow-left icon-white"></i> '.$this->getDbshopLang()->translate('返回订单').'</a>',
                //下面为线下支付使用
                'xxzf_title'        =>$this->getDbshopLang()->translate('线下支付说明'),
                'xxzf_input'        =>$this->getDbshopLang()->translate('支付凭证信息'),
                'xxzf_alert'        =>$this->getDbshopLang()->translate('请输入支付凭证信息证明您已经完成付款！'),
                'xxzf_return_url'   =>$this->getDbshopLang()->translate('返回订单列表'),
            
                'return_order'      =>$this->url()->fromRoute('frontorder/default'),
        );
        return $array;
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
     * 余额支付，进行付款
     * @param $orderInfo
     */
    private function userMoneyPayOper($orderInfo)
    {
        $userInfo = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')));
        //判断是否货币相同，如果不相同则进行错误提示
        if($orderInfo->currency != $this->getServiceLocator()->get('frontHelper')->getFrontDefaultCurrency())
        {
            $orderInfo->yezfPayState = 'currency_error';
            return $orderInfo;
        }
        //对账户余额进行当前货币汇率转换
        $userInfo->user_money = $this->getServiceLocator()->get('frontHelper')->shopPrice($userInfo->user_money);
        //判断用户是否有足够的余额进行付款
        if($userInfo->user_money < $orderInfo->order_amount) {
            $orderInfo->yezfPayState = 'false';
        } else {
            //开启数据库事务处理
            $this->getDbshopTable('dbshopTransaction')->DbshopTransactionBegin();

            $moneyChangedAmount = $userInfo->user_money - $orderInfo->order_amount;
            $moneyChangeNum     = '-'.$orderInfo->order_amount;
            $moneyLogArray      = array();
            $moneyLogArray['user_id']       = $userInfo->user_id;
            $moneyLogArray['user_name']       = $userInfo->user_name;
            $moneyLogArray['money_change_num']= $moneyChangeNum;
            $moneyLogArray['money_changed_amount'] = $moneyChangedAmount;
            $moneyLogArray['money_pay_state'] = 20;//20是已经处理（充值后者减值，10是待处理）
            $moneyLogArray['money_pay_type']  = 2;//支付类型，1充值，2消费，3提现，4退款
            $moneyLogArray['payment_code']    = 'yezf';
            $moneyLogArray['money_pay_info']  = $this->getDbshopLang()->translate('商品购买，订单编号为：').$orderInfo->order_sn;

            $state = $this->getDbshopTable('UserMoneyLogTable')->addUserMoneyLog($moneyLogArray);
            if($state) {
                //对会员表中的余额总值进行更新
                $moneyUpdateState = false;
                if($moneyLogArray['money_changed_amount'] >= 0) {
                    $this->getDbshopTable('UserTable')->updateUser(array('user_money'=>$moneyLogArray['money_changed_amount']), array('user_id'=>$userInfo->user_id));
                    $moneyUpdateState = true;
                }
                if($moneyUpdateState) {
                    $this->getDbshopTable('dbshopTransaction')->DbshopTransactionCommit();//事务确认
                    $orderInfo->yezfPayState = 'true';
                } else {
                    $this->getDbshopTable('dbshopTransaction')->DbshopTransactionRollback();//事务回滚
                    $orderInfo->yezfPayState = 'false';
                }
            } else {
                $orderInfo->yezfPayState = 'false';
            }

        }

        return $orderInfo;
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