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

use Zend\Filter\HtmlEntities;
use Zend\Mvc\Controller\AbstractActionController;
use User\FormValidate\FormUserValidate;
use Zend\Form\Element\Csrf;
use Zend\Session\Container;

class UserController  extends AbstractActionController
{
    private $dbTables = array();
    private $translator;
    /**
     * The default action - show the home page
     */
    public function indexAction ()
    {
        return $this->redirect()->toRoute('mobile/default');
    }
    /**
     * 会员登录
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function loginAction ()
    {
        //判断是否为手机端访问
        if(!$this->getServiceLocator()->get('frontHelper')->isMobile()) return $this->redirect()->toRoute('frontuser/default',array('action'=>'login'));

        if($this->getServiceLocator()->get('frontHelper')->getUserSession('user_id') != '')
            return $this->redirect()->toRoute('mobile/default');

        $this->layout()->title_name = $this->getDbshopLang()->translate('会员登录');

        $array = array();

        $filter = new HtmlEntities();

        //判断登录项中是否开启了邮箱登录
        $userEmailLoginState = $this->getServiceLocator()->get('frontHelper')->getRegOrLoginIni('login_email_state');
        $array['email_login_state'] = $userEmailLoginState;
        //判断登录项中是否开启手机号码登录
        $userPhonLoginState = $this->getServiceLocator()->get('frontHelper')->getRegOrLoginIni('login_phone_state');
        $array['phone_login_state'] = $userPhonLoginState;

        if($this->request->isPost()) {
            $userArray = $this->request->getPost()->toArray();
            $httpReferer = !empty($userArray['http_referer']) ? $filter->filter($userArray['http_referer']) : '';
            unset($userArray['http_referer']);
            //如果验证码开启
            if($this->getServiceLocator()->get('frontHelper')->websiteCaptchaState('user_login_captcha') == 'true') {
                $captchaSession = new Container('captcha');
                if(strtolower($captchaSession->word) != strtolower($userArray['captcha_code'])) {
                    exit($this->getDbshopLang()->translate('验证码错误！')  . '&nbsp;<a href="">' . $this->getDbshopLang()->translate('返回') . '</a>');
                }
            }
            unset($userArray['captcha_code']);

            //服务器端数据校验
            $userValidate = new FormUserValidate($this->getDbshopLang());
            $userValidate->checkUserForm($this->request->getPost(), 'login');

            $userNameWhere = '(user_name="'.$filter->filter($userArray['user_name']).'"';
            if($userEmailLoginState == 'true') $userNameWhere .= ' or user_email="'.$filter->filter($userArray['user_name']).'"';    //开启邮箱登录
            if($userPhonLoginState == 'true') $userNameWhere .= ' or user_phone="'.$filter->filter($userArray['user_name']).'"';     //开启手机号码登录
            $userNameWhere .= ')';

            $userArray['user_password'] = $this->getServiceLocator()->get('frontHelper')->getPasswordStr($userArray['user_password']);
            $userInfo = $this->getDbshopTable('UserTable')->infoUser(array('user_password'=>$userArray['user_password'], $userNameWhere));
            if($userInfo
                and $userInfo->user_password == $userArray['user_password']
                and in_array($userArray['user_name'], array($userInfo->user_name, $userInfo->user_email, $userInfo->user_phone))) {
                //当会员状态处于2（关闭）3（待审核）时，不进行登录操作
                $exitMessage = '';
                if($userInfo->user_state == 2) $exitMessage = $this->getDbshopLang()->translate('您的帐户处于关闭状态！')  . '&nbsp;<a href="' .$this->url()->fromRoute('mobile/default'). '">' . $this->getDbshopLang()->translate('返回首页') . '</a>';
                if($userInfo->user_state == 3) $exitMessage = $this->getDbshopLang()->translate('您的帐户处于待审核状态！') . '&nbsp;<a href="' .$this->url()->fromRoute('mobile/default'). '">' . $this->getDbshopLang()->translate('返回首页') . '</a>';
                if($exitMessage != '') exit($exitMessage);

                //根据等级积分判读，当前登录用户是否需要调整等级
                $groupId = $this->getDbshopTable('UserGroupTable')->checkUserGroup(array('group_id'=>$userInfo->group_id, 'integral_num'=>$userInfo->integral_type_2_num));
                if($groupId) {
                    $this->getDbshopTable('UserTable')->updateUser(array('group_id'=>$groupId), array('user_id'=>$userInfo->user_id));
                    $userInfo->group_id = $groupId;
                }

                $userGroup = $this->getDbshopTable('UserGroupExtendTable')->infoUserGroupExtend(array('group_id'=>$userInfo->group_id,'language'=>$this->getDbshopLang()->getLocale()));
                //session处理
                $sessionUser = array(
                    'user_name'      => $userInfo->user_name,
                    'user_id'        => $userInfo->user_id,
                    'user_email'     => $userInfo->user_email,
                    'user_phone'     => $userInfo->user_phone,
                    'group_id'       => $userInfo->group_id,
                    'user_group_name'=> $userGroup->group_name,
                    'user_avatar'    => (!empty($userInfo->user_avatar) ? $userInfo->user_avatar : $this->getServiceLocator()->get('frontHelper')->getUserIni('default_avatar'))
                );
                $this->getServiceLocator()->get('frontHelper')->setUserSession($sessionUser);
                //事件驱动
                $this->getEventManager()->trigger('user.login.front.post', $this, array('values'=>$sessionUser));
                //如果有返回网址，转向返回网址
                if($httpReferer != '') {
                    @header("Location: " . urldecode($httpReferer));
                    exit();
                }

                return $this->redirect()->toRoute('mobile/default');
            } else {
                $array['message'] = $this->getDbshopLang()->translate('登录失败，用户名或密码错误，请重新登录！');
            }
        }

        $queryHttpReferer = '';
        if($this->request->isGet()) {
            $queryArray = $this->request->getQuery()->toArray();
            if(isset($queryArray['http_referer']) and !empty($queryArray['http_referer'])) $queryHttpReferer = $filter->filter($queryArray['http_referer']);
        }
        $array['http_referer'] = (isset($httpReferer) and $httpReferer != '') ? $httpReferer : (!empty($queryHttpReferer) ? $queryHttpReferer : urlencode($this->getRequest()->getServer('HTTP_REFERER')));

        //登录的csrf
        $csrf = new Csrf('login_security');
        $csrf->setCsrfValidatorOptions(array('timeout'=>120, 'salt'=>'d56b699830e77ba53855679cb1d252da'));
        $array['login_csrf'] = $csrf->getAttributes();

        //统计使用
        $this->layout()->dbTongJiPage = 'user_login';

        return $array;
    }
    /**
     * 会员注册
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function registerAction ()
    {
        if($this->getServiceLocator()->get('frontHelper')->getUserSession('user_id') != '')
            return $this->redirect()->toRoute('mobile/default');

        $this->layout()->title_name = $this->getDbshopLang()->translate('会员注册');

        $filter = new HtmlEntities();

        if($this->request->isPost()) {
            //判断是否关闭了注册功能
            if($this->getServiceLocator()->get('frontHelper')->getUserIni('user_register_state') == 'false') {
                exit($this->getServiceLocator()->get('frontHelper')->getUserIni('register_close_message'));
            }

            //判断注册项中是否开启了邮箱注册
            $userEmailRegisterState = $this->getServiceLocator()->get('frontHelper')->getRegOrLoginIni('register_email_state');
            //判断注册项中是否开启手机号码
            $userPhoneRegisterState = $this->getServiceLocator()->get('frontHelper')->getRegOrLoginIni('register_phone_state');

            //服务器端数据验证
            $userValidate = new FormUserValidate($this->getDbshopLang());
            if($userEmailRegisterState != 'true' and $userPhoneRegisterState != 'true') $userValidate->checkUserForm($this->request->getPost(), 'register');
            if($userEmailRegisterState == 'true' and $userPhoneRegisterState == 'true') $userValidate->checkUserForm($this->request->getPost(), 'registerall');
            if($userEmailRegisterState == 'true' and $userPhoneRegisterState != 'true') $userValidate->checkUserForm($this->request->getPost(), 'registerandemail');
            if($userEmailRegisterState != 'true' and $userPhoneRegisterState == 'true') $userValidate->checkUserForm($this->request->getPost(), 'registerandphone');

            //注册验证状态，null 无需验证，email电邮验证，audit人工验证
            $audit = $this->getServiceLocator()->get('frontHelper')->getUserIni('register_audit');
            //是否发送欢迎邮件
            $welcomeEmail = $this->getServiceLocator()->get('frontHelper')->getUserIni('welcomeemail');

            $userArray = $this->request->getPost()->toArray();
            $httpReferer = !empty($userArray['http_referer']) ? $filter->filter($userArray['http_referer']) : '';

            //如果手机验证码开启
            if($this->getServiceLocator()->get('frontHelper')->websiteCaptchaState('phone_user_register_captcha') == 'true') {
                $phoneCaptchaSession = new Container('phone_captcha');
                if($phoneCaptchaSession->phone != $userArray['user_phone']) {
                    exit($this->getDbshopLang()->translate('与输入的手机号码不匹配！')  . '&nbsp;<a href="">' . $this->getDbshopLang()->translate('返回') . '</a>');
                }
                if($phoneCaptchaSession->captcha != $userArray['phone_captcha']) {
                    exit($this->getDbshopLang()->translate('与输入的手机验证码不匹配！')  . '&nbsp;<a href="">' . $this->getDbshopLang()->translate('返回') . '</a>');
                }
            }
            //如果验证码开启
            if($this->getServiceLocator()->get('frontHelper')->websiteCaptchaState('user_register_captcha') == 'true') {
                $captchaSession = new Container('captcha');
                if(strtolower($captchaSession->word) != strtolower($userArray['captcha_code'])) {
                    exit($this->getDbshopLang()->translate('验证码错误！')  . '&nbsp;<a href="">' . $this->getDbshopLang()->translate('返回') . '</a>');
                }
            }

            $addUser = array();
            $addUser['user_name'] = trim($userArray['user_name']);
            if($userEmailRegisterState == 'true') $addUser['user_email'] = trim($userArray['user_email']);
            if($userPhoneRegisterState == 'true') $addUser['user_phone'] = trim($userArray['user_phone']);
            $addUser['user_time']   = time();
            $addUser['group_id']    = $this->getServiceLocator()->get('frontHelper')->getUserIni('default_group_id');
            $addUser['user_state']  = (($audit == 'email' or $audit == 'audit') ? 3 : 1);//默认状态
            $addUser['user_password']= $this->getServiceLocator()->get('frontHelper')->getPasswordStr($userArray['user_password']);

            $addState = $this->getDbshopTable('UserTable')->addUser($addUser);
            if($addState) {
                //事件驱动
                $userArray['user_id'] = $addState;
                $this->getEventManager()->trigger('user.register.front.pre', $this, array('values'=>$userArray));
                //初始积分处理
                $userIntegralType = $this->getDbshopTable('UserIntegralTypeTable')->listUserIntegralType(array('e.language'=>$this->getDbshopLang()->getLocale()));
                if(is_array($userIntegralType) and !empty($userIntegralType)) {
                    foreach($userIntegralType as $integralTypeValue) {
                        if($integralTypeValue['default_integral_num'] > 0) {
                            $integralLogArray = array();
                            $integralLogArray['user_id']           = $addState;
                            $integralLogArray['user_name']         = $userArray['user_name'];
                            $integralLogArray['integral_log_info'] = $this->getDbshopLang()->translate('会员注册默认起始积分数：') . $integralTypeValue['default_integral_num'];
                            $integralLogArray['integral_num_log']  = $integralTypeValue['default_integral_num'];
                            $integralLogArray['integral_log_time'] = time();
                            //默认消费积分
                            if($integralTypeValue['integral_type_mark'] == 'integral_type_1') {
                                $this->getDbshopTable('UserTable')->updateUserIntegralNum(array('integral_num_log'=>$integralTypeValue['default_integral_num']), array('user_id'=>$addState));

                                $integralLogArray['integral_type_id'] = 1;
                                $this->getDbshopTable('IntegralLogTable')->addIntegralLog($integralLogArray);
                            }
                            //默认等级积分
                            if($integralTypeValue['integral_type_mark'] == 'integral_type_2') {
                                $this->getDbshopTable('UserTable')->updateUserIntegralNum(array('integral_num_log'=>$integralTypeValue['default_integral_num']), array('user_id'=>$addState), 2);

                                $integralLogArray['integral_type_id'] = 2;
                                $this->getDbshopTable('IntegralLogTable')->addIntegralLog($integralLogArray);
                            }
                        }
                    }
                }

                $userGroup = $this->getDbshopTable('UserGroupExtendTable')->infoUserGroupExtend(array('group_id'=>$addUser['group_id'],'language'=>$this->getDbshopLang()->getLocale()));
                //判断是否对注册成功的会员发送欢迎电邮
                if($welcomeEmail == 'true' and $userEmailRegisterState == 'true') {
                    $sourceArray  = array(
                        '{username}',
                        '{shopname}',
                        '{adminemail}',
                        '{time}',
                        '{shopnameurl}'
                    );
                    $replaceArray = array(
                        $userArray['user_name'],
                        $this->getServiceLocator()->get('frontHelper')->websiteInfo('shop_name'),
                        $this->getServiceLocator()->get('frontHelper')->websiteInfo('shop_email'),
                        date("Y-m-d H:i", time()),
                        '<a href="'. $this->getServiceLocator()->get('frontHelper')->dbshopHttpOrHttps() . $this->getServiceLocator()->get('frontHelper')->dbshopHttpHost() . $this->url()->fromRoute('mobile/default') . '" target="_blank">' . $this->getServiceLocator()->get('frontHelper')->websiteInfo('shop_name') . '</a>',
                    );
                    $registerEmail = array(
                        'send_user_name'=> $userArray['user_name'],
                        'send_mail'     => $userArray['user_email'],
                        'subject'       => str_replace($sourceArray, $replaceArray, $this->getServiceLocator()->get('frontHelper')->getUserIni('welcome_email_title')),
                        'body'          => nl2br(str_replace($sourceArray, $replaceArray, $this->getServiceLocator()->get('frontHelper')->getUserIni('welcome_email_body'))),
                    );
                    try {
                        $this->getServiceLocator()->get('shop_send_mail')->toSendMail($registerEmail);
                    } catch (\Exception $e) {

                    }
                }
                //当验证为电邮验证或者人工验证时进行处理
                $exitMessage = '';
                if($audit == 'email' and $userEmailRegisterState == 'true') {
                    $userAuditCode = md5($userArray['user_name']) . md5(time());
                    $auditUrl      = $this->getServiceLocator()->get('frontHelper')->dbshopHttpOrHttps() . $this->getServiceLocator()->get('frontHelper')->dbshopHttpHost() . $this->url()->fromRoute('frontuser/default', array('action'=>'userAudit')) . '?userName=' . urlencode($userArray['user_name']) . '&auditCode=' . $userAuditCode;
                    //将生成的审核码更新到会员表中
                    $this->getDbshopTable('UserTable')->updateUser(array('user_audit_code'=>$userAuditCode),array('user_id'=>$addState));
                    $auditEmail = array(
                        'send_user_name'=> $userArray['user_name'],
                        'send_mail'     => $userArray['user_email'],
                        'subject'       => $this->getServiceLocator()->get('frontHelper')->websiteInfo('shop_name') . $this->getDbshopLang()->translate('会员注册审核邮件'),
                        'body'          => $this->getDbshopLang()->translate('亲爱的') . $userArray['user_name'] . $this->getDbshopLang()->translate('您好，感谢您注册我们的会员，请点击会员审核链接进行认证审核 ') . '<a href="'.$auditUrl.'" target="_blank">'
                            . $this->getDbshopLang()->translate('点击审核会员 ') . '</a><br>' . $this->getDbshopLang()->translate('如果您无法点击审核链接，请复制下面的链接地址在浏览器中打开，完成审核 ') . '<br>' . $auditUrl
                    );
                    try {
                        $this->getServiceLocator()->get('shop_send_mail')->toSendMail($auditEmail);
                        $exitMessage = $this->getDbshopLang()->translate('您的帐户注册成功，需要验证邮件后方可使用，请登录您的邮箱进行验证！') . '&nbsp;<a href="' .$this->url()->fromRoute('mobile/default'). '">' . $this->getDbshopLang()->translate('返回首页') . '</a>';
                    } catch (\Exception $e) {
                        exit($this->getDbshopLang()->translate('发送验证邮件失败，请联系网站管理员进行处理'));
                    }
                }
                if($audit == 'audit') $exitMessage = $this->getDbshopLang()->translate('您的帐户注册成功，需要人工审核后才可使用！')  . '&nbsp;<a href="' .$this->url()->fromRoute('mobile/default'). '">' . $this->getDbshopLang()->translate('返回首页') . '</a>';
                if($exitMessage != '') exit($exitMessage);

                $sessionUser = array(
                    'user_name' =>$userArray['user_name'],
                    'user_id'   =>$addState,
                    'user_email'=>isset($userArray['user_email']) ? $userArray['user_email'] : '',
                    'group_id'  =>$addUser['group_id'],
                    'user_group_name'=>$userGroup->group_name,
                    'user_avatar'=>$this->getServiceLocator()->get('frontHelper')->getUserIni('default_avatar')
                );
                //注册成功session赋值，如果需要审核则不进行此操作
                $this->getServiceLocator()->get('frontHelper')->setUserSession($sessionUser);
                //事件驱动
                $this->getEventManager()->trigger('user.register.front.post', $this, array('values'=>$sessionUser));

                //如果有返回网址，转向返回网址
                if($httpReferer != '') {
                    @header("Location: " . urldecode($httpReferer));
                    exit();
                }
                return $this->redirect()->toRoute('mobile/default');
            }
        }

        $array = array();
        $array['http_referer'] = (isset($httpReferer) and $httpReferer != '') ? $httpReferer : urlencode($this->getRequest()->getServer('HTTP_REFERER'));

        //验证码csrf
        $PhoneCaptchaState = $this->getServiceLocator()->get('frontHelper')->websiteCaptchaState('phone_user_register_captcha');
        if($PhoneCaptchaState == 'true') {
            $captchaCsrf = new Csrf('captcha_security');
            $captchaCsrf->setCsrfValidatorOptions(array('timeout'=>60));
            $array['captcha_csrf'] = $captchaCsrf->getAttributes();
        }

        //注册的csrf
        $csrf = new Csrf('register_security');
        $csrf->setCsrfValidatorOptions(array('timeout'=>240, 'salt'=>'9de4a97425678c5b1288aa70c1669a64'));
        $array['register_csrf'] = $csrf->getAttributes();

        //事件驱动
        $response = $this->getEventManager()->trigger('user.regshow.front.post', $this, array('values'=>$array));
        if(!$response->isEmpty()) {//判断是否为空
            $num = $response->count();//获取监听数量
            for($i = 0; $i < $num; $i++) {
                $preArray = $response->offsetGet($i);
                //当有返回值且不为空时，进行赋值处理
                if(!empty($preArray)) $array[key($preArray)] = current($preArray);

                unset($preArray);
            }
        }

        //统计使用
        $this->layout()->dbTongJiPage = 'user_register';

        return $array;
    }
    /**
     * 注册协议
     * @return array
     */
    public function registerInfoAction()
    {
        $array = array();
        $this->layout()->title_name = $this->getDbshopLang()->translate('注册协议');

        return $array;
    }
    /**
     * 找回密码
     */
    public function forgotpasswdAction ()
    {
        if($this->getServiceLocator()->get('frontHelper')->getUserSession('user_id') != '')
            return $this->redirect()->toRoute('fronthome/default');

        $array = array();

        if($this->request->isPost()) {
            $postArray = $this->request->getPost()->toArray();
            $userInfo  = $this->getDbshopTable('UserTable')->infoUser(array('user_name'=>$postArray['user_name'], 'user_email'=>$postArray['user_email']));
            if(isset($userInfo->user_name) and $userInfo->user_name != '') {
                //生成唯一码及url
                $editCode    = md5($userInfo->user_name . $userInfo->user_email) . md5(time());
                $editUrl     = $this->getServiceLocator()->get('frontHelper')->dbshopHttpOrHttps() . $this->getServiceLocator()->get('frontHelper')->dbshopHttpHost() . $this->url()->fromRoute('frontuser/default', array('action'=>'forgotpasswdedit')) . '?editcode=' . $editCode;
                //发送的邮件内容
                $forgotEmail = array(
                    'send_user_name'=> $userInfo->user_name,
                    'send_mail'     => $userInfo->user_email,
                    'subject'       => $this->getServiceLocator()->get('frontHelper')->websiteInfo('shop_name') . $this->getDbshopLang()->translate('会员密码修改'),
                    'body'          => $this->getDbshopLang()->translate('亲爱的') . $userInfo->user_name . '<br>' . $this->getDbshopLang()->translate('您好，请点击下面的链接进行密码修改') . '<a href="'.$editUrl.'" target="_blank">'
                        . $this->getDbshopLang()->translate('点击修改密码 ') . '</a><br>' . $this->getDbshopLang()->translate('如果您无法点击修改链接，请复制下面的链接地址在浏览器中打开，完成密码修改 ') . '<br>' . $editUrl
                );

                try {
                    $this->getServiceLocator()->get('shop_send_mail')->toSendMail($forgotEmail);
                    $this->getDbshopTable('UserTable')->updateUser(array('user_forgot_passwd_code'=>$editCode),array('user_id'=>$userInfo->user_id));
                    $array['message'] = sprintf($this->getDbshopLang()->translate('已经向您的邮箱 %s 发送了一封邮件，请根据邮件内容完成新密码设定'),  $userInfo->user_email);
                } catch (\Exception $e) {
                    $array['message'] = $this->getDbshopLang()->translate('无法向您的邮箱发送邮件，请联系管理员处理！');
                }
            } else {
                $array['message'] = $this->getDbshopLang()->translate('您输入的信息错误，没有匹配的会员信息！') . $this->getDbshopLang()->translate('请重新输入');
            }
            echo '<script>alert("'.$array['message'].'");</script>';
        }

        return $array;
    }
    /**
     * 通过手机号码重置密码
     * @return array|\Zend\Http\Response
     */
    public function phoneforgotpasswdAction()
    {
        if($this->getServiceLocator()->get('frontHelper')->getUserSession('user_id') != '')
            return $this->redirect()->toRoute('fronthome/default');

        $array = array();

        $this->layout()->title_name = $this->getDbshopLang()->translate('重置密码');

        if($this->request->isPost()) {
            $postArray = $this->request->getPost()->toArray();

            $phoneState = $this->getServiceLocator()->get('frontHelper')->checkPhoneNum($postArray['user_phone']);//preg_match('#^13[\d]{9}$|^14[\d]{9}$|^15[\d]{9}$|^17[\d]{9}$|^18[\d]{9}$#', $postArray['user_phone']) ? 'true' : 'false';
            if($phoneState == 'false') {
                $array['message'] = $this->getDbshopLang()->translate('不正确的手机号码！');
                echo '<script>alert("'.$array['message'].'");</script>';
            } else {
                $where     = "user_name='".$postArray['user_phone']."' or user_phone='".$postArray['user_phone']."'";
                $userInfo  = $this->getDbshopTable('UserTable')->infoUser(array($where));
                if(empty($userInfo)) {
                    $array['message'] = $this->getDbshopLang()->translate('没有找到匹配的用户！');
                    echo '<script>alert("'.$array['message'].'");</script>';
                } else {
                    $array['user_name'] = $userInfo->user_name;
                    $array['user_phone'] = $postArray['user_phone'];
                    $array['step'] = 2;

                    $forgotPasswd = new Container('forgot_passwd');
                    $forgotPasswd->user_name    = $array['user_name'];
                    $forgotPasswd->user_phone   = $array['user_phone'];
                }
            }
        }

        return $array;
    }
    /**
     * 重置密码（通过手机号码）
     * @return array|\Zend\Http\Response
     */
    public function phoneforgotpasswdeditAction()
    {
        if($this->getServiceLocator()->get('frontHelper')->getUserSession('user_id') != '')
            return $this->redirect()->toRoute('fronthome/default');

        $array = array();

        $this->layout()->title_name = $this->getDbshopLang()->translate('新密码设定');

        $forgotPasswdSession = new Container('forgot_passwd');
        $phoneCaptchaSession = new Container('phone_captcha');
        if(empty($phoneCaptchaSession->captcha) or empty($forgotPasswdSession->user_name) or empty($forgotPasswdSession->user_phone)) return $this->redirect()->toRoute('frontuser/default', array('action'=>'phoneforgotpasswd'));

        if($this->request->isPost()) {
            $postArray = $this->request->getPost()->toArray();

            if($postArray['step'] == 'step3') {//新密码设置
                if($postArray['phone_code'] != $phoneCaptchaSession->captcha) return $this->redirect()->toRoute('m_user/default', array('action'=>'phoneforgotpasswd'));

                $array['user_name'] = $forgotPasswdSession->user_name;
                $this->getDbshopTable('UserTable')->updateUser(array('user_forgot_passwd_code'=>$postArray['phone_code']),array('user_name'=>$forgotPasswdSession->user_name));

            }
            if($postArray['step'] == 'step4') {//修改密码
                $userInfo  = $this->getDbshopTable('UserTable')->infoUser(array('user_name'=>$forgotPasswdSession->user_name, 'user_forgot_passwd_code'=>$phoneCaptchaSession->captcha));
                if(isset($userInfo->user_name) and $userInfo->user_name != '') {
                    if($postArray['user_password'] != $postArray['user_com_passwd']) {
                        $array['message'] = $this->getDbshopLang()->translate('两次输入的密码不相同！');
                    } else {
                        //设定新密码，并擦除找回密码唯一码
                        $this->getDbshopTable('UserTable')->updateUser(array('user_password'=>$this->getServiceLocator()->get('frontHelper')->getPasswordStr($postArray['user_password']), 'user_forgot_passwd_code'=>'no'),array('user_id'=>$userInfo->user_id));

                        $session = new \Zend\Session\Container();
                        $session->getManager()->getStorage()->clear('forgot_passwd');
                        $session->getManager()->getStorage()->clear('phone_captcha');
                        $array['message'] = $this->getDbshopLang()->translate('您的新密码已经修改生效，请登录测试') . '<a href="'. $this->url()->fromRoute('m_user/default', array('action'=>'login')) .'">' . $this->getDbshopLang()->translate('登录') . '</a>';
                    }
                } else {
                    $array['message'] = $this->getDbshopLang()->translate('该密码修改无效，请确认其正确性，如有疑问请联系管理员处理！');
                }
            }
        }

        return $array;
    }
    /**
     * 第三方登录跳转处理
     */
    public function otherloginAction()
    {
        $loginType = $this->request->getQuery('login_type');

        $loginService     = $this->checkOtherLoginConfig($loginType);
        $loginService->toLogin();
    }
    /**
     * 第三方注册操作
     */
    public function otherregisterAction()
    {
        $this->layout()->title_name = $this->getDbshopLang()->translate('会员信息补充');

        $array = array();

        $lType            = $this->params('login_type');
        $loginType        = 'QQ';
        if($lType !='' and $lType != 'qq') {
            $loginType = ucfirst($lType);
        }

        //验证从第三方回调获取的信息是否完整
        $loginService     = $this->checkOtherLoginConfig($lType);
        $openId           = $loginService->getOpenId();
        $otherUserInfo    = $loginService->getOtherInfo();

        if($loginType == 'Weixinphone' and !empty($otherUserInfo['unionid'])) {
            $openId = $otherUserInfo['unionid'];
        }

        //获取注册项信息，注册时是否需要填写邮箱
        $userEmailOtherLoginState           = $this->getServiceLocator()->get('frontHelper')->getRegOrLoginIni('other_login_email_state');
        $array['other_login_email_state']   = $userEmailOtherLoginState;

        if($this->request->isPost()) {
            //判断是否关闭了注册功能
            if($this->getServiceLocator()->get('frontHelper')->getUserIni('user_register_state') == 'false') {
                exit($this->getServiceLocator()->get('frontHelper')->getUserIni('register_close_message'));
            }

            $otherLoginType = $this->request->getPost('other_logion_type');
            if(empty($otherLoginType)) $otherLoginType = 'register';

            //服务器端数据验证
            $userValidate = new FormUserValidate($this->getDbshopLang());
            if($otherLoginType == 'login') {//判断是否是绑定
                $userValidate->checkUserForm($this->request->getPost(), 'otherlogin');
            } else {//新增账户
                if($userEmailOtherLoginState == 'true') $userValidate->checkUserForm($this->request->getPost(), 'otherregister');
                else $userValidate->checkUserForm($this->request->getPost(), 'othernoemailregister');
            }

            //会员数据插入处理
            $userArray = $this->request->getPost()->toArray();

            if($otherLoginType == 'login') {//当是绑定账户操作时
                $userPasswd = $this->getServiceLocator()->get('frontHelper')->getPasswordStr($userArray['login_user_passwd']);
                $userInfo = $this->getDbshopTable('UserTable')->infoUser(array('user_password'=>$userPasswd, 'user_name'=>$userArray['login_user_name']));
                if($userInfo) {

                    $otherLoginArray = array(
                        'user_id'       => $userInfo->user_id,
                        'open_id'       => $openId,
                        'ol_add_time'   => time(),
                        'login_type'    => $loginType
                    );
                    $this->getDbshopTable('OtherLoginTable')->addOtherLogin($otherLoginArray);
                    $loginService->clearLoginSession();

                    //当会员状态处于2（关闭）3（待审核）时，不进行登录操作
                    $exitMessage = '';
                    if($userInfo->user_state == 2) $exitMessage = $this->getDbshopLang()->translate('您的帐户处于关闭状态！')  . '&nbsp;<a href="' .$this->url()->fromRoute('mobile/default'). '">' . $this->getDbshopLang()->translate('返回首页') . '</a>';
                    if($userInfo->user_state == 3) $exitMessage = $this->getDbshopLang()->translate('您的帐户处于待审核状态！') . '&nbsp;<a href="' .$this->url()->fromRoute('mobile/default'). '">' . $this->getDbshopLang()->translate('返回首页') . '</a>';
                    if($exitMessage != '') exit($exitMessage);

                    //根据等级积分判读，当前登录用户是否需要调整等级
                    $groupId = $this->getDbshopTable('UserGroupTable')->checkUserGroup(array('group_id'=>$userInfo->group_id, 'integral_num'=>$userInfo->integral_type_2_num));
                    if($groupId) {
                        $this->getDbshopTable('UserTable')->updateUser(array('group_id'=>$groupId), array('user_id'=>$userInfo->user_id));
                        $userInfo->group_id = $groupId;
                    }

                    $userGroup = $this->getDbshopTable('UserGroupExtendTable')->infoUserGroupExtend(array('group_id'=>$userInfo->group_id,'language'=>$this->getDbshopLang()->getLocale()));
                    //session处理
                    $sessionUser = array(
                        'user_name'      => $userInfo->user_name,
                        'user_id'        => $userInfo->user_id,
                        'user_email'     => $userInfo->user_email,
                        'user_phone'     => $userInfo->user_phone,
                        'group_id'       => $userInfo->group_id,
                        'user_group_name'=> $userGroup->group_name,
                        'user_avatar'    => (!empty($userInfo->user_avatar) ? $userInfo->user_avatar : $this->getServiceLocator()->get('frontHelper')->getUserIni('default_avatar'))
                    );
                    $this->getServiceLocator()->get('frontHelper')->setUserSession($sessionUser);
                    //事件驱动
                    $this->getEventManager()->trigger('user.login.front.post', $this, array('values'=>$sessionUser));

                    return $this->redirect()->toRoute('mobile/default');
                } else {
                    $array['message'] = $this->getDbshopLang()->translate('登录失败，用户名或密码错误，请重新登录！');
                }
            }
            else
            {

                //注册验证状态，null 无需验证，email电邮验证，audit人工验证
                $audit = $this->getServiceLocator()->get('frontHelper')->getUserIni('register_audit');
                //是否发送欢迎邮件
                $welcomeEmail = $this->getServiceLocator()->get('frontHelper')->getUserIni('welcomeemail');

                //开启数据库事务处理
                $this->getDbshopTable('dbshopTransaction')->DbshopTransactionBegin();
                //异常开启，如果产生异常，则执行事务回归操作
                try {
                    $userArray['user_time']     = time();
                    $userArray['group_id']      = $this->getServiceLocator()->get('frontHelper')->getUserIni('default_group_id');
                    $userArray['user_state']    = (($audit == 'email' or $audit == 'audit') ? 3 : 1);//默认状态
                    $userArray['user_password'] = $this->getServiceLocator()->get('frontHelper')->getPasswordStr($openId);
                    if(isset($userArray['user_money'])) $userArray['user_money'] = 0;

                    $addState = $this->getDbshopTable('UserTable')->addUser($userArray);
                    //事件驱动
                    $userArray['user_id'] = $addState;
                    $this->getEventManager()->trigger('user.register.front.pre', $this, array('values'=>$userArray));
                    //初始积分处理
                    $userIntegralType = $this->getDbshopTable('UserIntegralTypeTable')->listUserIntegralType(array('e.language'=>$this->getDbshopLang()->getLocale()));
                    if(is_array($userIntegralType) and !empty($userIntegralType)) {
                        foreach($userIntegralType as $integralTypeValue) {
                            if($integralTypeValue['default_integral_num'] > 0) {
                                $integralLogArray = array();
                                $integralLogArray['user_id']           = $addState;
                                $integralLogArray['user_name']         = $userArray['user_name'];
                                $integralLogArray['integral_log_info'] = $this->getDbshopLang()->translate('会员注册默认起始积分数：') . $integralTypeValue['default_integral_num'];
                                $integralLogArray['integral_num_log']  = $integralTypeValue['default_integral_num'];
                                $integralLogArray['integral_log_time'] = time();
                                //默认消费积分
                                if($integralTypeValue['integral_type_mark'] == 'integral_type_1') {
                                    $this->getDbshopTable('UserTable')->updateUserIntegralNum(array('integral_num_log'=>$integralTypeValue['default_integral_num']), array('user_id'=>$addState));

                                    $integralLogArray['integral_type_id'] = 1;
                                    $this->getDbshopTable('IntegralLogTable')->addIntegralLog($integralLogArray);
                                }
                                //默认等级积分
                                if($integralTypeValue['integral_type_mark'] == 'integral_type_2') {
                                    $this->getDbshopTable('UserTable')->updateUserIntegralNum(array('integral_num_log'=>$integralTypeValue['default_integral_num']), array('user_id'=>$addState), 2);

                                    $integralLogArray['integral_type_id'] = 2;
                                    $this->getDbshopTable('IntegralLogTable')->addIntegralLog($integralLogArray);
                                }
                            }
                        }
                    }

                    $userGroup = $this->getDbshopTable('UserGroupExtendTable')->infoUserGroupExtend(array('group_id'=>$userArray['group_id'],'language'=>$this->getDbshopLang()->getLocale()));

                    $otherLoginArray = array(
                        'user_id'       => $addState,
                        'open_id'       => $openId,
                        'ol_add_time'   => $userArray['user_time'],
                        'login_type'    => $loginType
                    );
                    $addOtherLogin = $this->getDbshopTable('OtherLoginTable')->addOtherLogin($otherLoginArray);

                } catch (\Exception $e) {
                    $this->getDbshopTable('dbshopTransaction')->DbshopTransactionRollback();//事务回滚
                    exit($this->getDbshopLang()->translate('会员注册失败，请联系管理员进行处理！'));
                }
                $this->getDbshopTable('dbshopTransaction')->DbshopTransactionCommit();//事务确认

                if($addOtherLogin) {
                    //判断是否对注册成功的会员发送欢迎电邮
                    if($welcomeEmail == 'true' and $userEmailOtherLoginState == 'true') {
                        $sourceArray  = array(
                            '{username}',
                            '{shopname}',
                            '{adminemail}',
                            '{time}',
                            '{shopnameurl}'
                        );
                        $replaceArray = array(
                            $userArray['user_name'],
                            $this->getServiceLocator()->get('frontHelper')->websiteInfo('shop_name'),
                            $this->getServiceLocator()->get('frontHelper')->websiteInfo('shop_email'),
                            date("Y-m-d H:i", time()),
                            '<a href="'. $this->getServiceLocator()->get('frontHelper')->dbshopHttpOrHttps() . $this->getServiceLocator()->get('frontHelper')->dbshopHttpHost() . $this->url()->fromRoute('shopfront/default') . '" target="_blank">' . $this->getServiceLocator()->get('frontHelper')->websiteInfo('shop_name') . '</a>',
                        );
                        $registerEmail = array(
                            'send_user_name'=> $userArray['user_name'],
                            'send_mail'     => $userArray['user_email'],
                            'subject'       => str_replace($sourceArray, $replaceArray, $this->getServiceLocator()->get('frontHelper')->getUserIni('welcome_email_title')),
                            'body'          => nl2br(str_replace($sourceArray, $replaceArray, $this->getServiceLocator()->get('frontHelper')->getUserIni('welcome_email_body'))),
                        );
                        try {
                            $this->getServiceLocator()->get('shop_send_mail')->toSendMail($registerEmail);
                        } catch (\Exception $e) {

                        }
                    }
                    //当验证为电邮验证或者人工验证时进行处理
                    $exitMessage = '';
                    if($audit == 'email' and $userEmailOtherLoginState == 'true') {
                        $userAuditCode = md5($userArray['user_name']) . md5(time());
                        $auditUrl      = $this->getServiceLocator()->get('frontHelper')->dbshopHttpOrHttps() . $this->getServiceLocator()->get('frontHelper')->dbshopHttpHost() . $this->url()->fromRoute('frontuser/default', array('action'=>'userAudit')) . '?userName=' . urlencode($userArray['user_name']) . '&auditCode=' . $userAuditCode;
                        //将生成的审核码更新到会员表中
                        $this->getDbshopTable('UserTable')->updateUser(array('user_audit_code'=>$userAuditCode),array('user_id'=>$addState));
                        $auditEmail = array(
                            'send_user_name'=> $userArray['user_name'],
                            'send_mail'     => $userArray['user_email'],
                            'subject'       => $this->getServiceLocator()->get('frontHelper')->websiteInfo('shop_name') . $this->getDbshopLang()->translate('会员注册审核邮件'),
                            'body'          => $this->getDbshopLang()->translate('亲爱的') . $userArray['user_name'] . $this->getDbshopLang()->translate('您好，感谢您注册我们的会员，请点击会员审核链接进行认证审核 ') . '<a href="'.$auditUrl.'" target="_blank">'
                                . $this->getDbshopLang()->translate('点击审核会员 ') . '</a><br>' . $this->getDbshopLang()->translate('如果您无法点击审核链接，请复制下面的链接地址在浏览器中打开，完成审核 ') . '<br>' . $auditUrl
                        );
                        try {
                            $this->getServiceLocator()->get('shop_send_mail')->toSendMail($auditEmail);
                            $exitMessage = $this->getDbshopLang()->translate('您的帐户注册成功，需要验证邮件后方可使用，请登录您的邮箱进行验证！') . '&nbsp;<a href="' .$this->url()->fromRoute('shopfront/default'). '">' . $this->getDbshopLang()->translate('返回首页') . '</a>';
                        } catch (\Exception $e) {
                            exit($this->getDbshopLang()->translate('发送验证邮件失败，请联系网站管理员进行处理'));
                        }
                    }
                    if($audit == 'audit') $exitMessage = $this->getDbshopLang()->translate('您的帐户注册成功，需要人工审核后才可使用！')  . '&nbsp;<a href="' .$this->url()->fromRoute('shopfront/default'). '">' . $this->getDbshopLang()->translate('返回首页') . '</a>';
                    if($exitMessage != '') exit($exitMessage);

                    //注册成功session赋值，如果需要审核则不进行此操作
                    $sessionUser = array(
                        'user_name'         =>$userArray['user_name'],
                        'user_id'           =>$addState,
                        'user_email'        =>$userArray['user_email'],
                        'group_id'          =>$userArray['group_id'],
                        'user_group_name'   =>$userGroup->group_name,
                        'user_avatar'       =>$this->getServiceLocator()->get('frontHelper')->getUserIni('default_avatar')
                    );
                    $this->getServiceLocator()->get('frontHelper')->setUserSession($sessionUser);

                    //事件驱动
                    $this->getEventManager()->trigger('user.register.front.post', $this, array('values'=>$sessionUser));

                    //清空第三方登录中设置过的session值
                    $loginService->clearLoginSession();

                    return $this->redirect()->toRoute('mobile/default');
                }

            }

        }

        $array['open_id']           = $openId;
        $array['other_user_info']   = $otherUserInfo;

        return $array;
    }
    /**
     * 会员登出
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function loginoutAction ()
    {
        $userSession = new \Zend\Session\Container();
        $userSession->getManager()->getStorage()->clear('user_info');

        @header("Location: " . $this->getRequest()->getServer('HTTP_REFERER'));
        exit;
    }
    /**
     * 客户信息验证
     */
    public function checkAction ()
    {
        $checkType = $this->params('check_type');
        $userId    = (int) $this->params('user_id',0);

        $userInfo  = '';
        if($checkType == 'user_name') {
            $userName       = trim($this->request->getPost('param'));
            //对是否用户名被限制检查
            $retainState    = true;
            $registerRetain = $this->getServiceLocator()->get('frontHelper')->getUserIni('register_retain');
            if($registerRetain != '') {
                $registerRetain = explode('|', $registerRetain);
                if(in_array($userName, $registerRetain)) $retainState = false;
            }
            //检查是否存在
            $userInfo = $this->getDbshopTable()->infoUser(array('user_name'=>$userName,'user_id!='.$userId));

            exit(((($userInfo and $userInfo->user_id != 0) or !$retainState) ? json_encode(array('info'=>$this->getDbshopLang()->translate('该用户名已经存在！'), 'status'=>'n')) : 'y'));
        }
        if($checkType == 'user_email') {
            $userInfo = $this->getDbshopTable()->infoUser(array('user_email'=>trim($this->request->getPost('param')),'user_id!='.$userId));
            exit((($userInfo and $userInfo->user_id != 0) ? json_encode(array('info'=>$this->getDbshopLang()->translate('该电子邮箱已经存在！'), 'status'=>'n')) : 'y'));
        }
        if($checkType == 'user_phone') {
            $userInfo = $this->getDbshopTable()->infoUser(array('user_phone'=>trim($this->request->getPost('param')),'user_id!='.$userId));
            exit((($userInfo and $userInfo->user_id != 0) ? json_encode(array('info'=>$this->getDbshopLang()->translate('该手机号码已经存在！'), 'status'=>'n')) : 'y'));
        }

        exit();
    }
    /**
     * 检查第三方登录配置
     * @param string $loginType
     * @return array|object
     */
    private function checkOtherLoginConfig($loginType='qq')
    {
        if(empty($loginType)) $loginType = 'qq';

        $getClass = ucfirst($loginType).'Login';
        $loginService     = $this->getServiceLocator()->get($getClass);

        $loginConfigState = $loginService->getLoginConfigState();
        if(is_string($loginConfigState) and $loginConfigState == 'configError') exit($this->getDbshopLang()->translate('该登录方式的配置信息错误，必须在公网上进行测试！'));

        if($loginType == '' or $loginType == 'qq') {
            $loginService->redirectUri = $this->getServiceLocator()->get('frontHelper')->dbshopHttpOrHttps() . $this->getServiceLocator()->get('frontHelper')->dbshopHttpHost() . $this->url()->fromRoute('frontuser/default',array('action'=>'othercallback'));
        } else {
            $loginService->redirectUri = $this->getServiceLocator()->get('frontHelper')->dbshopHttpOrHttps() . $this->getServiceLocator()->get('frontHelper')->dbshopHttpHost() . $this->url()->fromRoute('frontuser/default/other_login_type',array('action'=>'othercallback', 'login_type'=>$loginType));
        }

        return $loginService;
    }
    /**
     * 数据表调用
     * @param string $tableName
     * @return multitype:
     */
    private function getDbshopTable ($tableName = 'UserTable')
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
        if (!$this->translator) {
            $this->translator = $this->getServiceLocator()->get('translator');
        }
        return $this->translator;
    }
} 