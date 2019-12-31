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

use User\FormValidate\FormUserValidate;
use Zend\Config\Reader\Ini;

class HomeController extends MobileHomeController
{
    private $dbTables = array();
    private $translator;

    public function indexAction ()
    {
        $array = array();
        //顶部title使用
        $this->layout()->title_name = $this->getDbshopLang()->translate('个人中心');

        //统计使用
        $this->layout()->dbTongJiPage= 'user_home';

        $userId = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');
        //用户信息
        $array['user_info'] = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$userId));

        //我的收藏
        $array['favorites_goods'] = $this->getDbshopTable('UserFavoritesTable')->favoritesNum(5, array('dbshop_user_favorites.user_id'=>$userId));

        //最新订单
        $array['order_list'] = $this->getDbshopTable('OrderTable')->allOrder(array('buyer_id'=>$userId, 'order_state NOT IN (0,60)'), array(), 5);

        //我的咨询
        $array['goods_ask_list'] = $this->getDbshopTable('GoodsAskTable')->numGoodsAsk(5, array('dbshop_goods_ask.ask_writer'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_name'), 'e.language'=>$this->getDbshopLang()->getLocale()));

        //虚拟商品
        $array['virtual_goods'] = $this->getDbshopTable('OrderGoodsTable')->mobileListOrrderGoods(array('dbshop_order_goods.goods_type'=>2, 'dbshop_order_goods.buyer_id'=>$userId), 6);

        //优惠券统计信息
        $array['coupon_num'] = $this->getDbshopTable('UserCouponTable')->allStateNumCoupon($userId);

        return $array;
    }
    /**
     * 头像修改
     * @return array
     */
    public function userEditAvatarAction()
    {
        $array = array();
        //顶部title使用
        $this->layout()->title_name = $this->getDbshopLang()->translate('头像修改');

        $userId = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');

        if($this->request->isPost()) {
            $userArray = $this->request->getPost()->toArray();
            $userInfo  = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$userId));
            $userArray['old_user_avatar'] = (isset($userInfo->user_avatar) and !empty($userInfo->user_avatar)) ? $userInfo->user_avatar : '';

            //会员头像上传
            $userAvatar = $this->getServiceLocator()->get('shop_other_upload')->userAvatarUpload($userId, 'user_avatar', (isset($userArray['old_user_avatar']) ? $userArray['old_user_avatar'] : ''), $this->getServiceLocator()->get('adminHelper')->defaultShopSet('shop_user_avatar_width'), $this->getServiceLocator()->get('adminHelper')->defaultShopSet('shop_user_avatar_height'));
            $userArray['user_avatar'] = $userAvatar['image'];

            if($userArray['user_avatar'] != '' and $userArray['user_avatar'] != $this->getServiceLocator()->get('frontHelper')->getUserSession('user_avatar')) {
                $this->getServiceLocator()->get('frontHelper')->setUserSession(array('user_avatar'=>$userArray['user_avatar']));
            }
            $userEditArray = array();
            $userEditArray['user_avatar']   = $userArray['user_avatar'];
            $this->getDbshopTable('UserTable')->updateUser($userEditArray, array('user_id'=>$userId));

            return $this->redirect()->toRoute('m_home/default');
        }

        $array['user_info']  = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$userId));

        return $array;
    }
    /**
     * 邀请好友
     * @return array
     */
    public function invitationAction()
    {
        $array = array();
        //顶部title使用
        $this->layout()->title_name = $this->getDbshopLang()->translate('邀请好友');

        return $array;
    }
    /**
     * 优惠券
     * @return array
     */
    public function userCouponAction()
    {
        $array = array();
        //顶部title使用
        $this->layout()->title_name = $this->getDbshopLang()->translate('我的优惠券');
        //优惠券分页
        $state = $this->request->getQuery('state');
        if(!in_array($state, array('all', '0', '1', '2', '3'))) $state = 'all';
        $page = $this->params('page',1);
        $array['page']  = $page;
        $array['state'] = $state;
        $where['user_id']= $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');
        if($state != 'all') $where['coupon_use_state'] = $state;
        $array['user_coupon_list'] = $this->getDbshopTable('UserCouponTable')->listPageUserCoupon(array('page'=>$page, 'page_num'=>20), $where);
        $array['user_coupon_list']->setPageRange(3);
        //优惠券统计信息
        $array['coupon_num'] = $this->getDbshopTable('UserCouponTable')->allStateNumCoupon($where['user_id']);

        return $array;
    }
    /**
     * 我的积分
     * @return array
     */
    public function userIntegralAction()
    {
        $array = array();
        //顶部title使用
        $this->layout()->title_name = $this->getDbshopLang()->translate('我的积分');

        $userId = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');

        $array['user_info'] = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$userId));

        return $array;
    }
    /**
     * 我的余额
     * @return array
     */
    public function userMoneyAction()
    {
        $array = array();
        //顶部title使用
        $this->layout()->title_name = $this->getDbshopLang()->translate('我的余额');

        $userId = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');
        $array['user_info'] = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$userId));

        $searchArray  = array();
        $searchArray['user_id'] = $array['user_info']->user_id;

        $page = $this->params('page',1);

        $array['user_money_log'] = $this->getDbshopTable('UserMoneyLogTable')->listUserMoneyLog(array('page'=>$page, 'page_num'=>20), $searchArray, 'front');
        $array['user_money_log']->setPageRange(3);

        return $array;
    }
    /**
     * 充值处理
     * @return array
     */
    public function userAddMoneyAction()
    {
        $array = array();
        //顶部title使用
        $this->layout()->title_name = $this->getDbshopLang()->translate('账户充值');

        $searchArray  = array('money_pay_type'=>1);
        $searchArray['user_id'] = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');

        $page = $this->params('page',1);
        $array['user_paycheck_log'] = $this->getDbshopTable('PayCheckTable')->listPayCheck(array('page'=>$page, 'page_num'=>20), $searchArray, 'front');
        $array['user_paycheck_log']->setPageRange(3);

        //在线支付方式，用于充值处理
        $array['payment'] = $this->listPayment();

        return $array;
    }
    /**
     * 账户余额提现
     * @return array
     */
    public function withdrawAction()
    {
        $array = array();
        //顶部title使用
        $this->layout()->title_name = $this->getDbshopLang()->translate('余额提现');

        $userId = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');

        $array['user_info'] = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$userId));
        $searchArray['user_id'] = $array['user_info']->user_id;

        $page = $this->params('page',1);
        $array['user_withdraw_log'] = $this->getDbshopTable('WithdrawLogTable')->listWithdrawLog(array('page'=>$page, 'page_num'=>20), $searchArray, 'front');
        $array['user_withdraw_log']->setPageRange(3);

        return $array;
    }
    /**
     * 余额提现操作
     * @return array
     */
    public function withdrawOperAction()
    {
        $array = array();
        //顶部title使用
        $this->layout()->title_name = $this->getDbshopLang()->translate('余额提现');

        $userId = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');

        //检查是否在表中存在该用户的待处理申请
        $checkState = $this->checkWithdraw($userId);
        if($checkState != 'true') {
            $array['withdraw_state'] = false;
            $array['message']        = $checkState;
        } else $array['withdraw_state'] = true;

        if($this->request->isPost()) {
            if(!$array['withdraw_state']) return $array;

            $withdrawArray = $this->request->getPost()->toArray();
            //服务器端进行验证
            $withdrawValidate = new FormUserValidate($this->getDbshopLang());
            $withdrawValidate->checkUserForm($this->request->getPost(), 'withdrawLog');

            $withdrawArray['user_id']   = $userId;
            $withdrawArray['user_name'] = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_name');
            $withdrawArray['currency_code'] = $this->getServiceLocator()->get('frontHelper')->getFrontDefaultCurrency();

            $withdrawArray['money_change_num'] = (float) $withdrawArray['money_change_num'];
            if($withdrawArray['money_change_num'] <= 0) exit($this->getDbshopLang()->translate('提现金额必须大于0！'));

            //最后进行校验，提现金额是否大于账户余额
            $userInfo = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$withdrawArray['user_id']));
            if($userInfo->user_money < $withdrawArray['money_change_num']) exit($this->getDbshopLang()->translate('您的账户余额小于提现额，无法申请提现！'));

            $state = $this->getDbshopTable('WithdrawLogTable')->addWithdrawLog($withdrawArray);
            if($state) exit('true');
            else exit($this->getDbshopLang()->translate('提现申请失败！'));
        }

        $array['user_info'] = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$userId));

        return $array;
    }
    private function checkWithdraw($userId)
    {
        $withdrawInfo = $this->getDbshopTable('WithdrawLogTable')->infoWithdrawLog(array('user_id'=>$userId, 'withdraw_state'=>0));
        if(isset($withdrawInfo[0]) and !empty($withdrawInfo[0])) return $this->getDbshopLang()->translate('已经有一个正在处理中的提现申请，需要处理完毕后，才能进行再次申请。');
        return 'true';
    }
    /**
     * 商品咨询列表
     * @return multitype:unknown NULL
     */
    public function goodsaskAction ()
    {
        $array = array();
        //顶部title使用
        $this->layout()->title_name = $this->getDbshopLang()->translate('我的咨询');

        //咨询分页
        $page = (int) $this->params('page',1);
        $array['page']     = $page;
        $array['goods_ask_list'] = $this->getDbshopTable('GoodsAskTable')->listGoodsAsk(array('page'=>$page, 'page_num'=>10), array('dbshop_goods_ask.ask_writer'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_name'), 'e.language'=>$this->getDbshopLang()->getLocale()));
        $array['goods_ask_list']->setPageRange(3);

        return $array;
    }
    /**
     * 商品咨询删除
     */
    public function askdelAction ()
    {
        $askId = (int) $this->params('ask_id');
        $type        = $this->request->getQuery('type');
        if($askId > 0) $this->getDbshopTable('GoodsAskTable')->delGoodsAsk(array('ask_id'=>$askId, 'ask_writer'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_name')));

        if(isset($type) and $type == 'home') {
            return $this->redirect()->toRoute('m_home/default');
        }
        return $this->redirect()->toRoute('m_home/default', array('action'=>'goodsask'));
    }
    /**
     * 编辑会员基本信息
     * @return multitype:NULL
     */
    public function usereditAction ()
    {
        $array = array();
        //顶部title使用
        $this->layout()->title_name = $this->getDbshopLang()->translate('账户资料修改');

        $userId = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');

        if($this->request->isPost()) {
            //判断注册项中是否开启了邮箱注册
            $userEmailRegisterState = $this->getServiceLocator()->get('frontHelper')->getRegOrLoginIni('register_email_state');
            //判断注册项中是否开启手机号码
            $userPhoneRegisterState = $this->getServiceLocator()->get('frontHelper')->getRegOrLoginIni('register_phone_state');
            //服务器端数据验证
            $userValidate = new FormUserValidate($this->getDbshopLang());
            if($userEmailRegisterState == 'true' and $userPhoneRegisterState == 'true') $userValidate->checkUserForm($this->request->getPost(), 'homeUserAllEdit');
            if($userEmailRegisterState != 'true' and $userPhoneRegisterState == 'true') $userValidate->checkUserForm($this->request->getPost(), 'homeUserPhoneEdit');
            if($userEmailRegisterState == 'true' and $userPhoneRegisterState != 'true') $userValidate->checkUserForm($this->request->getPost(), 'homeUserEdit');

            $userArray = $this->request->getPost()->toArray();

            $checkEmail = $this->getDbshopTable('UserTable')->infoUser(array('user_id!='.$userId.' and user_email="'.$userArray['user_email'].'"'));
            if($checkEmail) {
                $message = $this->getDbshopLang()->translate('您修改的邮箱已经存在，请重新修改！');
                $locationUrl = '';
            } else {
                //对修改信息进行重新赋值
                $userEditArray = array();
                if(isset($userArray['user_sex'])) $userEditArray['user_sex'] = $userArray['user_sex'];
                if(isset($userArray['user_email'])) $userEditArray['user_email'] = $userArray['user_email'];
                if(isset($userArray['user_phone'])) $userEditArray['user_phone'] = $userArray['user_phone'];
                if(isset($userArray['user_birthday'])) $userEditArray['user_birthday'] = $userArray['user_birthday'];

                $this->getDbshopTable('UserTable')->updateUser($userEditArray, array('user_id'=>$userId));
                $this->getServiceLocator()->get('frontHelper')->setUserSession(array('user_email'=>$userArray['user_email']));//修改session的user_email
                $this->getServiceLocator()->get('frontHelper')->setUserSession(array('user_phone'=>$userArray['user_phone']));//修改session的user_phone
                $message = $this->getDbshopLang()->translate('会员信息修改成功！');
                $locationUrl = 'window.location.href="'.$this->url()->fromRoute('m_home/default').'";';
            }

            $userArray['user_id'] = $userId;
            $this->getEventManager()->trigger('user.edit.front.pre', $this, array('values'=>$userArray));

            echo '<script>alert("'.$message.'");'.$locationUrl.'</script>';
        }
        $array['user_info']  = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$userId));
        $array['user_group'] = $this->getDbshopTable('UserGroupExtendTable')->infoUserGroupExtend(array('group_id'=>$array['user_info']->group_id));

        //事件驱动
        $response = $this->getEventManager()->trigger('user.edit.front.post', $this, array('values'=>$array));
        if(!$response->isEmpty()) {//判断是否为空
            $num = $response->count();//获取监听数量
            for($i = 0; $i < $num; $i++) {
                $preArray = $response->offsetGet($i);
                //当有返回值且不为空时，进行赋值处理
                if(!empty($preArray)) $array[key($preArray)] = current($preArray);

                unset($preArray);
            }
        }

        return $array;
    }

    /**
     * 我的分销用户
     * @return array
     */
    public function distributionAction()
    {
        $array = array();
        //顶部title使用
        $this->layout()->title_name = $this->getDbshopLang()->translate('我的分销&收入');

        $userId = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');

        $iniRead= new Ini();
        $config = $iniRead->fromFile(DBSHOP_PATH . '/module/Extendapp/Dbdistribution/data/config.ini');
        $array['info'] = $config['distribution_info_phone'];

        //分销收入
        $fOneCost = $this->getDbshopTable('DistributionOrderTable')->distributionOrderCost(array('one_level_user_id'=>$userId, 'o_state'=>1));
        $nOneCost = $this->getDbshopTable('DistributionOrderTable')->distributionOrderCost(array('one_level_user_id'=>$userId, 'o_state'=>2));
        $fTopCost = $this->getDbshopTable('DistributionOrderTable')->distributionOrderCost(array('top_level_user_id'=>$userId, 'o_state'=>1), 'top');
        $nTopCost = $this->getDbshopTable('DistributionOrderTable')->distributionOrderCost(array('top_level_user_id'=>$userId, 'o_state'=>2), 'top');
        $array['f_cost'] = $fOneCost + $fTopCost;
        $array['n_cost'] = $nOneCost + $nTopCost;

        $page = $this->params('page',1);
        $array['user_list'] = $this->getDbshopTable('DistributionUserTable')->subDistributionUserList(array('page'=>$page, 'page_num'=>20), array('u.one_level_user_id='.$userId.' or u.top_level_user_id='.$userId), $userId);
        $array['page']      = $page;

        return $array;
    }

    /**
     * 会员密码修改更新
     * @return multitype:NULL Ambigous <string, string, NULL, multitype:NULL , multitype:string NULL >
     */
    public function userpasswdAction ()
    {
        $array = array();
        //顶部title使用
        $this->layout()->title_name = $this->getDbshopLang()->translate('密码修改');

        //判断是否是第三方登录用户，如果是判断是否未修改密码状态，如果是则取消修改密码时对于原始密码的要求
        $array['other_login_passwd'] = false;
        $otherLoginInfo = $this->getDbshopTable('OtherLoginTable')->infoOtherLogin(array('dbshop_other_login.user_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')));
        if($otherLoginInfo) {
            if($otherLoginInfo->user_password == $this->getServiceLocator()->get('frontHelper')->getPasswordStr($otherLoginInfo->open_id)) {
                $array['other_login_passwd'] = true;
            }
        }

        if($this->request->isPost()) {
            //服务器端数据验证
            $userValidate = new FormUserValidate($this->getDbshopLang());
            $userValidate->checkUserForm($this->request->getPost(), ($array['other_login_passwd'] ? 'homeOtherPasswd' : 'homeUserPasswd'));//第三方认证与站点注册用户的验证不一样

            $passwdArray = $this->request->getPost()->toArray();
            //对于原始密码的获取，当是第三方认证第一次修改密码时，不需要输入原始密码，所以需要程序获取默认密码
            $passwdArray['old_user_password'] = ($array['other_login_passwd'] ? $otherLoginInfo->open_id : $passwdArray['old_user_password']);

            $userInfo    = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')));
            //判断原始密码是否正确
            if($userInfo->user_password == $this->getServiceLocator()->get('frontHelper')->getPasswordStr($passwdArray['old_user_password'])) {
                $passwdArray['user_password'] = $this->getServiceLocator()->get('frontHelper')->getPasswordStr($passwdArray['user_password']);
                $this->getDbshopTable('UserTable')->updateUser(array('user_password'=>$passwdArray['user_password']), array('user_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')));

                $message = $this->getDbshopLang()->translate('会员密码修改成功！');
                $locationUrl = 'window.location.href="'.$this->url()->fromRoute('m_home/default').'";';
            } else {
                $message = $this->getDbshopLang()->translate('会员密码修改失败,原始密码错误！');
                $locationUrl = '';
            }
            echo '<script>alert("'.$message.'");'.$locationUrl.'</script>';
        }

        return $array;
    }
    /**
     * 获取支付列表（只显示线上支付，不包括 余额支付）
     */
    private function listPayment()
    {
        $paymentArray = array();
        $filePath      = DBSHOP_PATH . '/data/moduledata/Payment/';
        if(is_dir($filePath)) {
            $dh = opendir($filePath);
            while (false !== ($fileName = readdir($dh))) {
                if($fileName != '.' and $fileName != '..' and stripos($fileName, '.php') !== false and $fileName != '.DS_Store') {
                    $paymentInfo = include($filePath . $fileName);

                    if(!in_array($paymentInfo['editaction'], array('xxzf', 'hdfk', 'yezf', 'malipay'))) {

                        //判断是否显示在当前平台
                        if(isset($paymentInfo['payment_show']['checked']) and !empty($paymentInfo['payment_show']['checked'])) {
                            $showArray = is_array($paymentInfo['payment_show']['checked']) ? $paymentInfo['payment_show']['checked'] : array($paymentInfo['payment_show']['checked']);
                            if(!in_array('phone', $showArray) and !in_array('all', $showArray)) continue;
                            //在微信内不显示支付宝支付，因为微信不支持；在手机浏览器上不能显示微信支付，因为手机浏览器不支持
                            if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false){
                                if(in_array($paymentInfo['editaction'], array('alipay', 'malipay', 'wxh5pay'))) continue;
                            } else {
                                if(in_array($paymentInfo['editaction'], array('wxmpay', 'wxh5pay'))) continue;
                            }
                        } else continue;

                        //判断是否符合当前的货币要求
                        $currencyState = false;
                        if(isset($paymentInfo['payment_currency']['checked']) and !empty($paymentInfo['payment_currency']['checked'])) {
                            $currencyArray = is_array($paymentInfo['payment_currency']['checked']) ? $paymentInfo['payment_currency']['checked'] : array($paymentInfo['payment_currency']['checked']);
                            $currencyState = in_array($this->getServiceLocator()->get('frontHelper')->getFrontDefaultCurrency(), $currencyArray) ? true : false;
                        }

                        if($paymentInfo['payment_state']['checked'] == 1 and $currencyState) {
                            $paymentArray[] = $paymentInfo;
                        }
                    }

                }
            }
        }
        //排序操作
        usort($paymentArray, function ($a, $b) {
            if($a['payment_sort']['content'] == $b['payment_sort']['content']) {
                return 0;
            }
            return ($a['payment_sort']['content'] < $b['payment_sort']['content']) ? -1 : 1;
        });

        return $paymentArray;
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