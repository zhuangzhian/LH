<?php
/**
 * DBShop 电子商务系统
 *
 * ==========================================================================
 * @link      http://www.dbshop.net/
 * @copyright Copyright (c) 2012-2016 DBShop.net Inc. (http://www.dbshop.net)
 * @license   http://www.dbshop.net/license.html License
 * ==========================================================================
 *
 * @author    静静的风
 *
 */

namespace Mobile\Controller;

use Zend\View\Model\ViewModel;

class WxController extends MobileHomeController
{
    private $dbTables = array();
    private $translator;

    /**
     * 微信内付款地址
     * @return \Zend\Http\Response|ViewModel
     */
    public function indexAction()
    {
        //判读是否是微信浏览器
        if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') === false) return $this->redirect()->toRoute('mobile/default');

        $view = new ViewModel();
        $array= array();

        $httpHost = $this->getServiceLocator()->get('frontHelper')->dbshopHttpHost();
        $httpType = $this->getServiceLocator()->get('frontHelper')->dbshopHttpOrHttps();

        $orderId = $this->params('order_id');

        if(strpos($orderId, 'p') !== false) {//
            $payCheckId = intval(str_replace('p', '', $orderId));
            $paycheckInfo   = $this->getDbshopTable('PayCheckTable')->infoPayCheck(array('paycheck_id'=>$payCheckId));
            if($paycheckInfo->pay_code == '' or $paycheckInfo->pay_state > 10) { return $this->redirect()->toRoute('mobile/default'); }

            $paymentData = array(
                'shop_name' => $this->getServiceLocator()->get('frontHelper')->websiteInfo('shop_name'),
                'order_name'=> $this->getDbshopLang()->translate('会员充值'),
                'goods_name' => $this->getDbshopLang()->translate('会员充值，充值会员:').$paycheckInfo->user_name,
                'paycheck'=> $paycheckInfo,
                'notify_url'=> $httpType . $httpHost . $this->url()->fromRoute('m_wx/default/wx_order_id', array('action'=>'checkNotify', 'order_id'=>$payCheckId)),
                'wxreturn_url'=> $httpType . $httpHost . $this->url()->fromRoute('m_wx/default/wx_order_id', array('action'=>'index', 'order_id'=>$orderId.'p'))
            );
            $result = $this->getServiceLocator()->get('payment')->payServiceSet($paycheckInfo->pay_code)->paycheckPaymentTo($paymentData);
            $view->setTemplate('/mobile/home/paycheck_pay.phtml');

            $array = array(
                'jsApiParameters' => $result,
                'paycheck_id' => $payCheckId
            );

        } else {
            $orderId = intval($orderId);
            //订单基本信息
            $orderInfo  = $this->getDbshopTable('OrderTable')->infoOrder(array('order_id'=>$orderId));
            if($orderInfo->pay_code == '' or $orderInfo->buyer_id != $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')) { return $this->redirect()->toRoute('mobile/default'); }

            //订单配送信息
            $deliveryAddress = $this->getDbshopTable('OrderDeliveryAddressTable')->infoDeliveryAddress(array('order_id'=>$orderId));
            //订单商品
            $orderGoods = $this->getDbshopTable('OrderGoodsTable')->listOrderGoods(array('order_id'=>$orderId));
            //打包数据，传给下面的支付输出
            $paymentData = array(
                'shop_name' => $this->getServiceLocator()->get('frontHelper')->websiteInfo('shop_name'),
                'order'     => $orderInfo,
                'address'   => $deliveryAddress,
                'goods'     => $orderGoods,
                'notify_url'=> $httpType . $httpHost . $this->url()->fromRoute('m_wx/default/wx_order_id', array('action'=>'notify', 'order_id'=>$orderId)),
                'wxreturn_url'=> $httpType . $httpHost . $this->url()->fromRoute('m_wx/default/wx_order_id', array('action'=>'index', 'order_id'=>$orderId))
            );

            $result = $this->getServiceLocator()->get($orderInfo->pay_code)->paymentTo($paymentData);

            $view->setTemplate('/mobile/home/order_pay.phtml');

            $array = array(
                'jsApiParameters' => $result,
                'order_id' => $orderId
            );
        }

        $view->setVariables($array);
        return $view;
    }
    /**
     * 微信内支付返回地址
     */
    public function notifyAction()
    {

        $orderId = (int) $this->params('order_id');
        //订单基本信息
        $orderInfo  = $this->getDbshopTable('OrderTable')->infoOrder(array('order_id'=>$orderId));
        if($orderInfo->pay_code == '') exit();

        //语言包及支付处理，在支付模块中进行
        $language      = $this->paymentLanguage();
        $array = $this->getServiceLocator()->get($orderInfo->pay_code)->paymentNotify($orderInfo, $language);

        //付款成功
        if(isset($array['payFinish']) and $array['payFinish']) {
            $updateOrderArray = array('order_state'=>20, 'order_out_sn'=>(isset($_REQUEST['trade_no']) ? $_REQUEST['trade_no'] : ''), 'pay_time'=>time());

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
                    'ordersn'    => $sendArray['order_sn'],
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

        }

        exit($array['message']);
    }
    /**
     * 微信支付完成后返回页面（无论成功或者失败）
     */
    public function wxpayfinishAction()
    {
        //判读是否是微信浏览器
        if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') === false) return $this->redirect()->toRoute('mobile/default');

        $orderId    = (int) $this->params('order_id');
        $state      = trim($this->request->getQuery('state'));

        $view = new ViewModel();
        $view->setTemplate('/mobile/home/wx_pay_finish.phtml');
        $array = array(
            'state'     => $state,
            'order_id'  => $orderId
        );
        $view->setVariables($array);
        return $view;
    }

    /**
     * 微信内支付返回地址(充值)
     */
    public function checkNotifyAction()
    {
        $paycheckId     = (int) $this->params('order_id');
        $paycheckInfo   = $this->getDbshopTable('PayCheckTable')->infoPayCheck(array('paycheck_id'=>$paycheckId));
        if($paycheckInfo->pay_code == '') exit();

        $language      = $this->paymentLanguage();
        $array = $this->getServiceLocator()->get($paycheckInfo->pay_code)->paycheckPaymentNotify($paycheckInfo, $language);
        if(isset($array['payFinish']) and $array['payFinish']) {
            if($array['stateNum'] == 20) {//更新充值记录中的支付状态,付款完成
                $userInfo = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$paycheckInfo->user_id));
                $moneyLogArray['user_id']         = $userInfo->user_id;
                $moneyLogArray['user_name']       = $userInfo->user_name;
                $moneyLogArray['money_change_num']= $paycheckInfo->money_change_num;
                $moneyLogArray['money_pay_state'] = 20;//20是已经处理（充值后者减值，10是待处理）
                $moneyLogArray['money_pay_type']  = 1;//支付类型，1充值，2消费，3提现，4退款
                $moneyLogArray['money_changed_amount'] = $userInfo->user_money + $moneyLogArray['money_change_num'];
                $moneyLogArray['money_pay_info']  = $paycheckInfo->paycheck_info;

                $state = $this->getDbshopTable('UserMoneyLogTable')->addUserMoneyLog($moneyLogArray);
                if($state) {
                    //对会员表中的余额总值进行更新
                    $this->getDbshopTable('UserTable')->updateUser(array('user_money'=>$moneyLogArray['money_changed_amount']), array('user_id'=>$userInfo->user_id));
                    //更新充值记录中的支付状态
                    $this->getDbshopTable('PayCheckTable')->updatePayCheck(array('pay_state'=>20, 'paycheck_finish_time'=>time()), array('paycheck_id'=>$paycheckId));
                }
            }
        }
        exit($array['message']);
    }
    /**
     * 微信支付充值完成后返回页面（无论成功或者失败）
     */
    public function wxpaycheckfinishAction()
    {
        //判读是否是微信浏览器
        if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') === false) return $this->redirect()->toRoute('mobile/default');

        $paycheckId = (int) $this->params('order_id');
        $state      = trim($this->request->getQuery('state'));

        $view = new ViewModel();
        $view->setTemplate('/mobile/home/wx_paycheck_finish.phtml');
        $array = array(
            'state'         => $state,
            'paycheck_id'   => $paycheckId
        );
        $view->setVariables($array);
        return $view;
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
     * 在上面支付中需要用到的语言包
     * @return multitype:string NULL Ambigous <string, string, NULL, multitype:NULL , multitype:string NULL >
     */
    private function paymentLanguage()
    {
        $array = array(
            'order_total'       =>$this->getDbshopLang()->translate('订单总金额'),

            'return_order'      =>$this->url()->fromRoute('frontorder/default'),
        );
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