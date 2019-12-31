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
namespace Email\Common\Service;

use Qcloud\Sms\SmsMultiSender;

class SendPhoneSms
{
    private $smsConfig = array();

    public function __construct()
    {
        //获取短信的配置信息
        if(empty($this->smsConfig)) {
            $readerConfig        = new \Zend\Config\Reader\Ini();
            $this->smsConfig   = $readerConfig->fromFile(DBSHOP_PATH . '/data/moduledata/System/phonesms.ini');
        }

    }
    /**
     * 发送手机短信
     * @param $data
     * @param $user_phone
     * @param string $phone_template
     * @param string $user_id
     * @return bool
     */
    public function toSendSms($data, $user_phone, $phone_template='', $user_id = '') {

        //判断是否存在
        if(!isset($this->smsConfig['shop_phone_sms']['phone_sms_type']) or !isset($this->smsConfig['shop_phone_sms'][$phone_template])) return false;
        //判断是否开启了手机短信服务功能，如果未开启则不进行操作
        if($this->smsConfig['shop_phone_sms']['phone_sms_type'] == '' or $this->smsConfig['shop_phone_sms'][$phone_template] == '') return false;

        $user_phone = !empty($user_phone) ? (is_array($user_phone) ? implode(',', $user_phone) : $user_phone) : '';
        if(!empty($this->smsConfig['shop_phone_sms']['admin_phone'])) {
            if($phone_template == 'alidayu_submit_order_template_id' && $this->smsConfig['shop_phone_sms']['admin_submit_order_phone_message'] == 1)    $user_phone = !empty($user_phone) ? $user_phone.','.$this->smsConfig['shop_phone_sms']['admin_phone'] : $this->smsConfig['shop_phone_sms']['admin_phone'];
            if($phone_template == 'alidayu_payment_order_template_id' && $this->smsConfig['shop_phone_sms']['admin_payment_order_phone_message'] == 1)  $user_phone = !empty($user_phone) ? $user_phone.','.$this->smsConfig['shop_phone_sms']['admin_phone'] : $this->smsConfig['shop_phone_sms']['admin_phone'];
            if($phone_template == 'alidayu_ship_order_template_id' && $this->smsConfig['shop_phone_sms']['admin_ship_order_phone_message'] == 1)  $user_phone = !empty($user_phone) ? $user_phone.','.$this->smsConfig['shop_phone_sms']['admin_phone'] : $this->smsConfig['shop_phone_sms']['admin_phone'];
            if($phone_template == 'alidayu_finish_order_template_id' && $this->smsConfig['shop_phone_sms']['admin_finish_order_phone_message'] == 1)    $user_phone = !empty($user_phone) ? $user_phone.','.$this->smsConfig['shop_phone_sms']['admin_phone'] : $this->smsConfig['shop_phone_sms']['admin_phone'];
            if($phone_template == 'alidayu_cancel_order_template_id' && $this->smsConfig['shop_phone_sms']['admin_cancel_order_phone_message'] == 1)    $user_phone = !empty($user_phone) ? $user_phone.','.$this->smsConfig['shop_phone_sms']['admin_phone'] : $this->smsConfig['shop_phone_sms']['admin_phone'];
            //库存提醒只发送给管理员
            if($phone_template == 'alidayu_goods_stock_template_id') $user_phone = $this->smsConfig['shop_phone_sms']['admin_phone'];
        }
        if(empty($user_phone)) return false;

        if($this->smsConfig['shop_phone_sms']['phone_sms_type'] == 'alidayu') {//阿里大于发送短信
            $smsJson    = $this->createSmsArray($data);
            //$user_phone = is_array($user_phone) ? implode(',', $user_phone) : $user_phone;

            include(DBSHOP_PATH . '/vendor/alibaba/dayu/TopSdk.php');
            $c = new \TopClient();
            $c->appkey    = $this->smsConfig['shop_phone_sms']['alidayu_app_key'];
            $c->secretKey = $this->smsConfig['shop_phone_sms']['alidayu_app_secret'];

            $req = new \AlibabaAliqinFcSmsNumSendRequest();
            $req->setExtend($user_id);
            $req->setSmsType('normal');
            $req->setSmsFreeSignName($this->smsConfig['shop_phone_sms']['alidayu_sign_name']);
            $req->setRecNum($user_phone);
            $req->setSmsTemplateCode($this->smsConfig['shop_phone_sms'][$phone_template]);

            if(isset($data['sendMore']) && !empty($data['sendMore'])) {
                foreach ($data['sendMore'] as $dataValue) {
                    $smsJson    = $this->createSmsArray($dataValue);
                    $req->setSmsParam($smsJson);
                    $resp = $c->execute($req);
                }
            } else {
                $smsJson    = $this->createSmsArray($data);
                $req->setSmsParam($smsJson);
                $resp = $c->execute($req);
            }
        }

        if($this->smsConfig['shop_phone_sms']['phone_sms_type'] == 'aliyun') {//阿里云发送短信
            include(DBSHOP_PATH . '/vendor/alibaba/aliyunMNS/MNS-Sdk.php');
            $c = new \AliyunDbMns();
            $c->setSignName($this->smsConfig['shop_phone_sms']['aliyun_sign_name']);
            $c->setAppTopic($this->smsConfig['shop_phone_sms']['aliyun_app_topic']);
            $c->setAppKey($this->smsConfig['shop_phone_sms']['aliyun_app_key']);
            $c->setAppSecret($this->smsConfig['shop_phone_sms']['aliyun_app_secret']);
            $c->setAppEndpoint($this->smsConfig['shop_phone_sms']['aliyun_app_endpoint']);

            if(isset($data['sendMore']) && !empty($data['sendMore'])) {
                foreach ($data['sendMore'] as $dataValue) {
                    $c->setMnsTag($this->createTagArray($dataValue));
                    $c->sendMessage(time(), explode(',', $user_phone), $this->smsConfig['shop_phone_sms'][$phone_template]);
                }
            } else {
                $c->setMnsTag($this->createTagArray($data));
                $c->sendMessage(time(), explode(',', $user_phone), $this->smsConfig['shop_phone_sms'][$phone_template]);
            }
        }

        if($this->smsConfig['shop_phone_sms']['phone_sms_type'] == 'newaliyun') {//新阿里云发送短信
            include(DBSHOP_PATH . '/vendor/alibaba/newAliyunMNS/MNS-Sdk.php');
            $c = new \NewAliyunDbMns();
            $c->setSignName($this->smsConfig['shop_phone_sms']['new_aliyun_sign_name']);
            $c->setAppKey($this->smsConfig['shop_phone_sms']['new_aliyun_app_key']);
            $c->setAppSecret($this->smsConfig['shop_phone_sms']['new_aliyun_app_secret']);
            $c->setDomain('dysmsapi.aliyuncs.com');
            $c->setProduct('Dysmsapi');
            $c->setRegion('cn-hangzhou');
            $c->setEndpointName('cn-hangzhou');
            $c->setRegionId('cn-hangzhou');

            if(isset($data['sendMore']) && !empty($data['sendMore'])) {
                foreach ($data['sendMore'] as $dataValue) {
                    $smsJson = $this->createSmsArray($dataValue);
                    $c->sendMessage($smsJson, $user_phone, $this->smsConfig['shop_phone_sms'][$phone_template]);
                }
            } else {
                $smsJson = $this->createSmsArray($data);
                $c->sendMessage($smsJson, $user_phone, $this->smsConfig['shop_phone_sms'][$phone_template]);
            }
        }

        if($this->smsConfig['shop_phone_sms']['phone_sms_type'] == 'qqCloud') {//腾讯云短信
            include DBSHOP_PATH . "/vendor/tencent/sms/index.php";
            $smsSender  = new SmsMultiSender($this->smsConfig['shop_phone_sms']['qqcloud_app_key'], $this->smsConfig['shop_phone_sms']['qqcloud_app_secret']);
            if(isset($data['sendMore']) && !empty($data['sendMore'])) {
                foreach ($data['sendMore'] as $dataValue) {
                    $params     = $this->createQqCloudSmsArray($dataValue, $phone_template);
                    $result     = $smsSender->sendWithParam("86", explode(',', $user_phone), $this->smsConfig['shop_phone_sms'][$phone_template], $params, $this->smsConfig['shop_phone_sms']['qqcloud_sign_name']);
                }
            } else {
                $params     = $this->createQqCloudSmsArray($data, $phone_template);
                $result     = $smsSender->sendWithParam("86", explode(',', $user_phone), $this->smsConfig['shop_phone_sms'][$phone_template], $params, $this->smsConfig['shop_phone_sms']['qqcloud_sign_name']);
            }
        }

        return true;
    }

    /**
     * 对内容进行json处理（用于阿里大于）
     * @param $data
     * @return false|string
     */
    private function createSmsArray($data) {
        $bodyArray  = array(
            'shopname'      => (isset($data['shopname'])        ? $data['shopname']         : ''),
            'buyname'       => (isset($data['buyname'])         ? $data['buyname']          : ''),
            'username'      => (isset($data['username'])        ? $data['username']         : ''),
            'ordersn'       => (isset($data['ordersn'])         ? $data['ordersn']          : ''),
            'ordertotal'    => (isset($data['ordertotal'])      ? str_replace('&nbsp;', '', $data['ordertotal'])       : ''),
            'expressname'   => (isset($data['expressname'])     ? $data['expressname']      : ''),
            'expressnumber' => (isset($data['expressnumber'])   ? $data['expressnumber']    : ''),
            'goodsname'     => (isset($data['goodsname'])       ? $data['goodsname']        : ''),
            'goodsstock'    => (isset($data['goodsstock'])      ? $data['goodsstock']       : ''),
            'virtualaccount'=> (isset($data['virtualaccount'])  ? $data['virtualaccount']   : ''),//虚拟商品账号
            'virtualpassword'=> (isset($data['virtualpassword'])? $data['virtualpassword']  : ''),//虚拟商品密码

            'code'          => (isset($data['captcha'])         ? $data['captcha']          : ''),
            //'product'       => (isset($data['patcheashopname']) ? $data['patcheashopname']  : ''),
        );
        $bodyArray = array_filter($bodyArray);

        return json_encode($bodyArray);
    }
    /**
     * 设置标签数组，用于阿里云
     * @param $data
     * @return array
     */
    private function createTagArray($data) {
        $tagArray  = array(
            'shopname'      => (isset($data['shopname'])        ? $data['shopname']         : ''),
            'buyname'       => (isset($data['buyname'])         ? $data['buyname']          : ''),
            'username'      => (isset($data['username'])        ? $data['username']         : ''),
            'ordersn'       => (isset($data['ordersn'])         ? $data['ordersn']          : ''),
            'ordertotal'    => (isset($data['ordertotal'])      ? str_replace('&nbsp;', '', $data['ordertotal']) : ''),
            'expressname'   => (isset($data['expressname'])     ? $data['expressname']      : ''),
            'expressnumber' => (isset($data['expressnumber'])   ? $data['expressnumber']    : ''),
            'goodsname'     => (isset($data['goodsname'])       ? $data['goodsname']        : ''),
            'goodsstock'    => (isset($data['goodsstock'])      ? $data['goodsstock']       : ''),
            'virtualaccount'=> (isset($data['virtualaccount'])  ? $data['virtualaccount']   : ''),//虚拟商品账号
            'virtualpassword'=> (isset($data['virtualpassword'])? $data['virtualpassword']  : ''),//虚拟商品密码

            'code'          => (isset($data['captcha'])         ? $data['captcha']          : ''),
            //'product'       => (isset($data['patcheashopname']) ? $data['patcheashopname']  : ''),
        );
        $tagArray = array_filter($tagArray);

        return $tagArray;
    }

    /**
     * 腾讯云短信解析
     * @param $data
     * @param $templateType
     * @return array
     */
    private function createQqCloudSmsArray($data, $templateType)
    {
        $array = array();

        switch ($templateType) {
            case 'alidayu_submit_order_template_id';    //订单提交
            case 'alidayu_payment_order_template_id';   //付款完成
            case 'alidayu_ship_order_template_id';      //发货完成
            case 'alidayu_finish_order_template_id';    //订单完成，确认收货
            case 'alidayu_cancel_order_template_id';    //订单取消
                $array = array(
                    (isset($data['buyname']) ? $data['buyname'] : ''),
                    (isset($data['ordertotal']) ? str_replace('&nbsp;', '', $data['ordertotal']) : ''),
                    (isset($data['ordersn']) ? $data['ordersn'] : ''),
                    (isset($data['expressname']) ? $data['expressname'] : ''),
                    (isset($data['expressnumber']) ? $data['expressnumber'] : '')
                );
                break;


            case 'alidayu_goods_stock_template_id';     //缺货提醒
                $array = array(
                    (isset($data['shopname']) ? $data['shopname'] : ''),
                    (isset($data['goodsname']) ? $data['goodsname'] : ''),
                    (isset($data['goodsstock']) ? $data['goodsstock'] : '')
                );
                break;

            case 'alidayu_virtual_goods_send_template_id'; //虚拟商品发货
                $array = array(
                    (isset($data['shopname']) ? $data['shopname'] : ''),
                    (isset($data['goodsname']) ? $data['goodsname'] : ''),
                    (isset($data['virtualaccount']) ? $data['virtualaccount'] : ''),
                    (isset($data['virtualpassword']) ? $data['virtualpassword'] : '')
                );
                break;

            case 'alidayu_user_audit_template_id';      //会员审核通过
                $array = array(
                    (isset($data['shopname']) ? $data['shopname'] : ''),
                    (isset($data['username']) ? $data['username'] : '')
                );
                break;

            case 'alidayu_user_password_template_id';   //找回密码
            case 'alidayu_phone_captcha_template_id';   //手机验证码
                $array = array((isset($data['captcha']) ? $data['captcha'] : ''));
                break;
        }

        return array_filter($array);
    }
}