<?php
/**
 * DBShop 电子商务系统
 *
 * ==========================================================================
 * @link      http://www.dbshop.net/
 * @copyright Copyright (c) 2012-2018 DBShop.net Inc. (http://www.dbshop.net)
 * @license   http://www.dbshop.net/license.html License
 * ==========================================================================
 *
 * @author    静静的风
 *
 */

namespace User\Service\OtherLogin;

use Zend\Config\Reader\Ini;
use Zend\Session\Container;

include DBSHOP_PATH . '/vendor/alibaba/alipay/AopSdk.php';
class NewalipayLogin
{
    const VERSION       = "1.0";//DBShop自行加入的版本号

    private $gatewayUrl = 'https://openapi.alipay.com/gateway.do';

    private $authUrl    = 'https://openauth.alipay.com/oauth2/publicAppAuthorize.htm?';
    //private $appId      = '';
    //private $privateKey = '';
    //private $publicKey  = '';
    private $format     = 'json';
    private $charset    = 'UTF-8';
    private $signType   = 'RSA2';

    private $aopClient;
    private $loginConfig;
    private $loginSession;

    public $redirectUri;

    public function __construct()
    {
        $configReader = new Ini();
        if(empty($this->loginConfig) and file_exists(DBSHOP_PATH . '/data/moduledata/User/OtherLogin.ini')) {
            $config = $configReader->fromFile(DBSHOP_PATH . '/data/moduledata/User/OtherLogin.ini');
            if(isset($config['Newalipay']) and !empty($config['Newalipay'])) {
                $this->loginConfig  = $config['Newalipay'];

                $this->aopClient    = new \AopClient();
                $this->aopClient->gatewayUrl        = $this->gatewayUrl;
                $this->aopClient->appId             = $this->loginConfig['ali_app_id'];
                $this->aopClient->rsaPrivateKey     = $this->loginConfig['ali_private_key'];
                $this->aopClient->alipayrsaPublicKey= $this->loginConfig['ali_public_key'];
                $this->aopClient->format            = $this->format;
                $this->aopClient->postCharset       = $this->charset;
                $this->aopClient->signType          = $this->signType;
            }
        }
        if(empty($this->loginSession)) {
            $this->loginSession = new Container('new_alipay_login_session');
        }
    }
    /**
     * 获取当前的登录配置信息是否存在
     * @return string
     */
    public function getLoginConfigState()
    {
        if(empty($this->loginConfig) or $this->loginConfig['login_state'] == 'false') return 'configError';

        return true;
    }

    /**
     * 支付宝第一次请求
     */
    public function toLogin()
    {
        $this->authUrl .= 'app_id='.$this->loginConfig['ali_app_id'];
        $this->authUrl .= '&scope=auth_user';
        $this->authUrl .= '&redirect_uri=' . urlencode($this->redirectUri);
        $this->authUrl .= '&state=init';//这里可自定义

        @header("Location: " . $this->authUrl);
        exit;
    }

    public function callBack()
    {
        $appId      = $_REQUEST['app_id'];
        $authCode   = $_REQUEST['auth_code'];
        if($appId != $this->loginConfig['ali_app_id']) exit('AppID不一致');

        $request = new \AlipaySystemOauthTokenRequest();
        $request->setCode($authCode);
        $request->setGrantType("authorization_code");

        $result = $this->aopClient->execute($request);
        $responseNode   = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $accessToken     = $result->$responseNode->access_token;
        if(!empty($accessToken)) {
            $userRequest = new \AlipayUserInfoShareRequest();
            $userResult  = $this->aopClient->execute($userRequest, $result->$responseNode->access_token);
            $userResponseNode   = str_replace(".", "_", $userRequest->getApiMethodName()) . "_response";
            $userResultCode     = $userResult->$userResponseNode->code;
            if(!empty($userResultCode) && $userResultCode == 10000) {

                $this->loginSession->openid     = $userResult->$userResponseNode->user_id;
                $this->loginSession->userName   = $userResult->$userResponseNode->nick_name;

            } else exit($userResult->$responseNode->msg);

        } else exit($result->$responseNode->msg);
    }
    /**
     * 获取Id值，唯一值，可用来识别用户
     * @return mixed
     */
    public function getOpenId()
    {
        return $this->loginSession->openid;
    }
    /**
     * 获取唯一ID,只是为了统一，方便前台调用
     * @return mixed
     */
    public function getUnionId()
    {
        return $this->loginSession->openid;
    }
    /**
     * 获取会员信息
     * @return array
     */
    public function getOtherInfo()
    {
        return array('nickname'=>$this->loginSession->userName);
    }
    /**
     * 清除session
     */
    public function clearLoginSession()
    {
        $loginSession = new \Zend\Session\Container();
        $loginSession->getManager()->getStorage()->clear('new_alipay_login_session');
    }
}