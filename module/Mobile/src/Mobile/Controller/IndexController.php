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
use Zend\Session\Container;

class IndexController extends AbstractActionController
{
    private $dbTables = array();
    private $translator;

    public function indexAction()
    {
        //检查程序是否安装
        if(!file_exists(DBSHOP_PATH . '/data/install.lock')) {
            return $this->redirect()->toRoute('install/default');
        }

        //判断是否为手机端访问
        if(!$this->getServiceLocator()->get('frontHelper')->isMobile()) return $this->redirect()->toRoute('shopfront/default');

        //统计使用
        $this->layout()->dbTongJiPage = 'index';

        $array = array();
        $userGroupId = $this->getServiceLocator()->get('frontHelper')->getUserSession('group_id');
        //首页商品
        $array['index_goods'] = $this->getDbshopTable('GoodsTable')->mobileGoodsArray(array('dbshop_goods.goods_class_have_true=1', 'group_id'=>($userGroupId > 0 ? $userGroupId : 0)), 'dbshop_goods.goods_id DESC', 10);

        //首页幻灯片
        $adSlide           = $this->getDbshopTable('AdTable')->listAd(array('ad_class'=>'index', 'ad_type'=>'slide', 'template_ad'=>DBSHOP_TEMPLATE));
        if(isset($adSlide)) {
            $array['ad_slide'] = $this->getDbshopTable('AdSlideTable')->listAdSlide(array('ad_id'=>$adSlide[0]['ad_id']));
        }

        $shareSession = new Container('share');
        $uid = (int) $this->request->getQuery('user_id', 0);
        if($uid > 0 && (!isset($shareSession->distribution_user_id) || $shareSession->distribution_user_id != $uid)) {
            $shareSession->distribution_user_id = $uid;
        }

        if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
            //分享设置信息
            $jsSDK = new JssdkService();
            $array['signPackage'] = $jsSDK->getSignPackage();

            //在微信内访问，自动登录微信
            $userId = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');
            $otherLoginArray = $this->getServiceLocator()->get('frontHelper')->getUserOtherLoginIni();
            if($userId <= 0 && $otherLoginArray['Weixinphone']['login_state'] == 'true' && $otherLoginArray['Weixinphone']['weixin_auto_login'] == 1) {
                return $this->redirect()->toRoute('m_user/default', array('action'=>'otherlogin'), array('query'=>array('login_type'=>'weixinphone')));
            }
        }

        return $array;
    }
    public function checkphoneCaptchaAction()
    {
        $phoneCaptchaSession = new Container('phone_captcha');

        // 验证码验证，通过ajax验证
        $postCaptcha = $this->request->getPost('param');
        if (isset($phoneCaptchaSession->captcha) and $phoneCaptchaSession->captcha != '') {
            exit($postCaptcha == $phoneCaptchaSession->captcha ? json_encode(array('info'=>'', 'status'=>'y')) : json_encode(array('info'=>$this->getDbshopLang()->translate('手机验证码输入错误'), 'status'=>'n')));
        }
        exit();
    }
    /**
     * 验证码
     */
    public function checkCaptchaAction ()
    {
        $captchaSession = new Container('captcha');

        // 验证码验证，通过ajax验证
        $postCaptcha = $this->request->getPost('param');
        if (isset($captchaSession->word) and $captchaSession->word != '') {
            exit(strtolower($postCaptcha) == strtolower($captchaSession->word) ? json_encode(array('info'=>'', 'status'=>'y')) : json_encode(array('info'=>$this->getDbshopLang()->translate('验证码输入错误'), 'status'=>'n')));
        }
        exit();
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
