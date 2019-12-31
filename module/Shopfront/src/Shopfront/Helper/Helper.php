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

namespace Shopfront\Helper;

use Goods\Service\PromotionsRuleService;
use User\Service\IntegralRuleService;
use Zend\Config\Factory;
use Zend\Filter\StaticFilter;
use Zend\Session\Container;
use Zend\View\Helper\AbstractHelper;

class Helper extends AbstractHelper
{
    private $iniReader;
    private $userSession;     //会员session
    private $cartSession;     //购物车session
    private $orderStateArray; //订单状态
    private $goodsConfig;     //商品设置
    private $storageConfig;   //存储设置

    public  $currencySession; //货币session
    
    protected $dbshopSql;
    protected $dbshopResultSet;

    public function __construct ()
    {
        if(empty($this->iniReader)) {
            $this->iniReader = new \Zend\Config\Reader\Ini();
        }
        if(empty($this->storageConfig)) {
            $this->storageConfig = $this->iniReader->fromFile(DBSHOP_PATH . '/data/moduledata/Upload/Storage.ini');
        }
        if(empty($this->userSession)) {
            $this->userSession = new Container('user_info');
        }
        if(empty($this->currencySession)) {
            $this->currencySession = new Container('currency_session');
        }
        if(empty($this->cartSession)) {
            $this->cartSession = new Container('dbshop_cart');
            if(!isset($this->cartSession->cart)) {
                $this->cartSession->cart = array();
            }
        }
        if(empty($this->orderStateArray)) {
            $this->orderStateArray[0]  =  '已取消';
            $this->orderStateArray[10]  = '待付款';
            $this->orderStateArray[15]  = '付款中';
            $this->orderStateArray[20]  = '已付款';
            $this->orderStateArray[30]  = '待发货';
            $this->orderStateArray[40]  = '已发货';
            $this->orderStateArray[60]  = '订单完成';
        }
    }
    /**
     * 获取当前域名
     * @return mixed
     */
    public function dbshopHttpHost()
    {
        $httpHost = $_SERVER['HTTP_HOST'];
        return $httpHost;
    }
    /**
     * 获取当前访问协议是http:// 还是 https://
     * @return string
     */
    public function dbshopHttpOrHttps()
    {
        //$httpType = ((isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';

        $httpType = ((isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 1 || strtolower($_SERVER['HTTPS']) === 'on'))
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
            || (isset($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] == 443))) ? 'https://' : 'http://';

        return $httpType;
    }

    /**
     * 获取珑客服配置信息
     * @return array|\Zend\Config\Config
     */
    public function longImConfig()
    {
        $imConfig = array();
        if(file_exists(DBSHOP_PATH . '/data/moduledata/System/longIM.ini')) {
            $imConfig = Factory::fromFile(DBSHOP_PATH . '/data/moduledata/System/longIM.ini');
        }
        return $imConfig;
    }

    /**
     * 订单的配置信息获取
     * @param $name
     * @return mixed
     */
    public function getOrderConfig($name)
    {
        $array = $this->iniReader->fromFile(DBSHOP_PATH . '/data/moduledata/System/order.ini');
        if(isset($array[$name]) and is_string($array[$name])) return $array[$name];
    }
    /**
     * 获取系统设置信息
     * @param $name
     * @return mixed
     */
    public function websiteInfo ($name)
    {
        $array  = $this->iniReader->fromFile(DBSHOP_PATH . '/data/moduledata/System/config.ini');
        return (isset($array['shop_system'][$name]) ? $array['shop_system'][$name] : null);
    }

    /**
     * 获取前台底部图片
     * @return array
     */
    public function frontFooter()
    {
        $array = $this->iniReader->fromFile(DBSHOP_PATH . '/data/moduledata/System/footer.ini');
        return $array;
    }
    /**
     * 获取验证码设置信息
     * @param $name
     * @return null
     */
    public function websiteCaptchaState($name)
    {
        $patchaConfig = array();
        $captchaConfigFile = DBSHOP_PATH . '/data/moduledata/User/CaptchaConfig.ini';
        if(file_exists($captchaConfigFile)) $patchaConfig = $this->iniReader->fromFile($captchaConfigFile);
        else {
            $array = $this->iniReader->fromFile(DBSHOP_PATH . '/data/moduledata/System/config.ini');
            $patchaConfig = $array['shop_system'];
        };
        return (isset($patchaConfig[$name]) ? $patchaConfig[$name] : null);
    }
    /**
     * 获取支付信息
     * @param $payCode
     * @return array|mixed
     */
    public function websitePaymentInfo($payCode)
    {
        $paymentInfo = array();
        $filePath      = DBSHOP_PATH . '/data/moduledata/Payment/'.$payCode.'.php';
        if(file_exists($filePath)) {
            $paymentInfo = include $filePath;
        }
        return $paymentInfo;
    }
    /**
     * 获取插件的前台地址
     * @return array
     */
    public function frontExtendUrlArray()
    {
        $urlFile  = DBSHOP_PATH . '/data/moduledata/moduleini/fronturl.ini';
        $urlArray = array();
        if(file_exists($urlFile)) {
            $urlArray = $this->iniReader->fromFile($urlFile);
        }
        return $urlArray;
    }
    /**
     * 获取后台附件设置中，商品上传信息
     * @param $typeName
     * @param $valueName
     * @return mixed
     */
    public function getGoodsUploadIni($typeName, $valueName)
    {
        $array  = $this->iniReader->fromFile(DBSHOP_PATH . '/data/moduledata/Upload/Goods.ini');
        return (isset($array[$typeName][$valueName]) ? $array[$typeName][$valueName] : null);
    }
    /**
     * 获取分析设置信息（将被废除，慎用）
     * @param $valueName
     * @return string
     */
    public function getAnalyticsIni($valueName)
    {
        $fileIni = DBSHOP_PATH . '/data/moduledata/Analytics/Analytics.ini';
        if(file_exists($fileIni)) {
            $array  = $this->iniReader->fromFile($fileIni);
            return (isset($array[$valueName]) ? $array[$valueName] : null);
        }
        return '';
    }
    /**
     * 获取系统后台设置的内容信息及统计代码
     * @param $name
     * @return string
     */
    public function getSystemContent($name)
    {
        $array = array('buy_service'=>'buy_service.ini', 'buy'=>'buy.ini', 'goods_quality'=>'goods_quality.ini', 'statistics'=>'statistics.ini');
        if($array[$name] == '') return ;
        $content = @file_get_contents(DBSHOP_PATH . '/data/moduledata/System/' . $array[$name]);
        return $content;
    }
    /**
     * 获取会员设置信息
     * @param $name
     * @return mixed
     */
    public function getUserIni($name)
    {
        $array = $this->iniReader->fromFile(DBSHOP_PATH . '/data/moduledata/User/User.ini');
        return (isset($array[$name]) ? $array[$name] : null);
    }
    /**
     * 获取 注册与登录项 设置
     * @param $item
     * @return mixed
     */
    public function getRegOrLoginIni($item)
    {
        $array = $this->iniReader->fromFile(DBSHOP_PATH . '/data/moduledata/User/RegOrLogin.ini');
        return $array[$item];
    }
    /**
     * 获取第三方登录设置信息
     * @return array
     */
    public function getUserOtherLoginIni()
    {
        $array = array();
        if(file_exists(DBSHOP_PATH . '/data/moduledata/User/OtherLogin.ini')) {
            $array = $this->iniReader->fromFile(DBSHOP_PATH . '/data/moduledata/User/OtherLogin.ini');
        }

        if($this->isMobile()) {
            if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
                unset($array['Alipay'], $array['Newalipay'], $array['Weixin']);
            } elseif(strpos($_SERVER['HTTP_USER_AGENT'], 'Alipay') !== false) {
                unset($array['Weixinphone'], $array['Weixin']);
            } else unset($array['Weixin'], $array['Newalipay'], $array['Weixinphone']);
        } else {
            unset($array['Weixinphone']);
        }

        return $array;
    }
    /**
     * 检查第三方登录是否有开启的登录
     * @return string
     */
    public function getUserOtherLoginState()
    {
        $state = 'false';
        $array = array();
        if(file_exists(DBSHOP_PATH . '/data/moduledata/User/OtherLogin.ini')) {
            $array = $this->iniReader->fromFile(DBSHOP_PATH . '/data/moduledata/User/OtherLogin.ini');
        }

        if($this->isMobile()) {
            if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
                unset($array['Alipay'], $array['Newalipay'], $array['Weixin']);
            } elseif(strpos($_SERVER['HTTP_USER_AGENT'], 'Alipay') !== false) {
                unset($array['Weixinphone'], $array['Weixin']);
            } else unset($array['Weixin'], $array['Newalipay'], $array['Weixinphone']);
        } else {
            unset($array['Weixinphone']);
        }

        if(!empty($array)) {
            foreach($array as $value) {
                if(isset($value['login_state']) and $value['login_state'] == 'true') {
                    $state = 'true';
                    break;
                }
            }
        }
        return $state;
    }

    /**
     * 对用户名称的隐藏
     * @param $userName
     * @return string
     */
    public function userNameHide($userName)
    {
        $str      = mb_strlen($userName, 'utf-8');
        $firstStr = mb_substr($userName, 0, 1, 'utf-8');
        $lastStr  = mb_substr($userName, -1, 1, 'utf-8');
        return $str == 2 ? $firstStr . str_repeat('*', $str - 1) : $firstStr . str_repeat("*", $str - 2) . $lastStr;
    }
    /*-----------------------------------会员登录----------------------------------------*/
    /**
     * 设置会员登录session
     * @param $array
     */
    public function setUserSession ($array)
    {
        if(is_array($array) and !empty($array)) {
            foreach($array as $key => $val) {
                $this->userSession->$key = $val;
            }
        }
    }
    /**
     * 获取会员登录session中的值
     * @param $name
     * @return null|string
     */
    public function getUserSession ($name)
    {
        return (isset($this->userSession->$name) ? StaticFilter::execute($this->userSession->$name, 'HtmlEntities') : '');
    }
    /*-----------------------------------会员登录----------------------------------------*/
    
    /*-----------------------------------前台客服----------------------------------------*/
    /**
     * 前台客服
     * @param $fileName
     * @return string
     */
    public function getOnlineService($fileName)
    {
        if(file_exists(DBSHOP_PATH . '/data/moduledata/System/online/' . $fileName . '.php'))
        return @file_get_contents(DBSHOP_PATH . '/data/moduledata/System/online/' . $fileName . '.php');
        else
        return '';
    }
    /**
     * 获取客服样式
     * @return string
     */
    public function getOnlinestyle()
    {
        $onlineStyle = $this->iniReader->fromFile(DBSHOP_PATH . '/data/moduledata/System/Online.ini');
        if(isset($onlineStyle['online']) and $onlineStyle['online']['style'] != '') return $onlineStyle['online']['style'];
        return '';
    }
    /*-----------------------------------前台客服----------------------------------------*/
    
    /*-----------------------------------购物车处理----------------------------------------*/
    /**
     * 设置购物车商品
     * @param $key
     * @param $val
     */
    public function setCartSession ($key, $val)
    {
		//$this->cartSession->cart[$key] = $val;
		//这里进行一步处理，这是一个极个别的现象，使用 $this->cartSession->cart[$key] = $val; 这样的方式赋值不成功，所以才有下面的代码，其他购物车代码类似
		$array = $this->cartSession->cart;
		$array[$key] = $val;
		$this->cartSession->cart = $array;
    }
    /**
     * 编辑购物车商品
     * @param $key
     * @param $type
     * @param $value
     */
    public function editCartSession($key, $type, $value)
    {
		$array = $this->cartSession->cart;
		$array[$key][$type] = $value;
        $this->cartSession->cart = $array;
    }
    /**
     * 获取单个购物车内商品信息
     * @param $key
     * @param null $type
     * @return mixed
     */
    public function getCartOneGoodsSession($key, $type=null)
    {
		$array = $this->cartSession->cart;
        return ($type != '' ? $array[$key][$type] : $array[$key]);
    }
    /**
     * 检查购物车内该商品是否存在
     * @param $key
     * @return bool|null
     */
    public function checkCartSession ($key)
    {
		$array = $this->cartSession->cart;
        if(isset($array[$key]) and !empty($array[$key])) return null;
        return true;
    }
    /**
     * 购物车删除单个商品
     * @param $key
     */
    public function delCartSession ($key)
    {
		$array = $this->cartSession->cart;
        unset($array[$key]);
		$this->cartSession->cart = $array;
    }
    /**
     * 清空购物车商品
     */
    public function clearCartSession()
    {
    	$this->cartSession->getManager()->getStorage()->clear('dbshop_cart');
    }
    /**
     * 购物车商品种类数
     * @return int
     */
    public function cartGoodsNum ()
    {
        return count($this->cartSession->cart);
    }
    /**
     * 获取购物车商品总价
     * @return int
     */
    public function getCartTotal ()
    {
        $total = 0;
        foreach ($this->cartSession->cart as $value) {
            $total = $total + $this->shopPrice($value['goods_shop_price']) * $value['buy_num'];
        }
        return $total;
    }
    /**
     * 获取购物车商品重量
     * @return int
     */
    public function getCartTotalWeight ()
    {
        $totalWeight = 0;
        foreach ($this->cartSession->cart as $value) {
            $totalWeight = $totalWeight + $value['goods_weight'] * $value['buy_num'];
        }
        return $totalWeight;
    }
    /**
     * 获取购物车中可以使用的积分数（消费积分）
     * @return int
     */
    public function getCartTotalIntegral ()
    {
        $totalIntegral = 0;
        foreach ($this->cartSession->cart as $value) {
            if(isset($value['integral_num']) and $value['integral_num']>0) $totalIntegral = $totalIntegral + $value['integral_num'] * $value['buy_num'];
        }
        return $totalIntegral;
    }
    /**
     * 获取购物车
     * @return mixed
     */
    public function getCartSession ()
    {
        return $this->cartSession->cart;
    }
    /*-----------------------------------购物车处理----------------------------------------*/

    /*-----------------------------------购物过程中----------------------------------------*/
    public function promotionsOrIntegralFun(array $array, array $data=array())
    {
        //用户优惠和积分中的计算
        //$userGroup = $this->getServiceLocator()->get('frontHelper')->getUserSession('group_id');
        $userGroup = isset($data['group_id']) ? $data['group_id'] : '';
        //优惠金额计算结果
        $promotionsRuleService = new PromotionsRuleService();
        $array['promotionsCost'] = $promotionsRuleService->promotionsRuleCalculation(array('cartGoods'=>$array['cart_array'], 'user_group'=>$userGroup));

        //获取积分计算结果
        $integralRuleService = new IntegralRuleService();
        $array['integralInfo'] = $integralRuleService->integralRuleCalculation(array('cartGoods'=>$array['cart_array'], 'user_group'=>$userGroup));     //消费积分
        $array['integralInfo1'] = $integralRuleService->integralRuleCalculation(array('cartGoods'=>$array['cart_array'], 'user_group'=>$userGroup), 2);//等级积分

        return $array;
    }
    /*-----------------------------------购物过程中----------------------------------------*/

    /*-----------------------------------订单相关----------------------------------------*/
    public function createOrderSn($userId='')
    {
        //唯一性订单编号
        if(empty($userId)) $orderSn = time().str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        else $orderSn = time().rand(0, 9).rand(0, 9).(strlen($userId) > 4 ? substr($userId, -4) : str_pad($userId, 4, '0', STR_PAD_LEFT));
        return $orderSn;
    }
    /**
     * 获取订单状态数组
     * @return mixed
     */
    public function getOrderState()
    {
        return $this->orderStateArray;
    }
    /**
     * 获取订单状态单独信息
     * @param $orderState
     * @return mixed
     */
    public function getOneOrderStateInfo($orderState)
    {
        return $this->orderStateArray[$orderState];
    }
    /*-----------------------------------订单相关----------------------------------------*/
    
    /*-----------------------------------货币信息----------------------------------------*/
    /**
     * 获取货币信息设置
     * @return array
     */
    public function getFrontCurrency()
    {
        $array = $this->iniReader->fromFile(DBSHOP_PATH . '/data/moduledata/Currency/Currency.ini');
        $currencyArray = array();
        if(is_array($array) and !empty($array)) {
            foreach($array as $key => $value) {
                if($value['currency_state'] == 1) $currencyArray[$key] = $value;
            }
        }
        return $currencyArray;
    }
    /**
     * 获取默认货币信息内容
     * @param bool $currencySession
     * @return mixed|Container
     */
    public function getFrontDefaultCurrency($currencySession=false)
    {
        if(isset($this->currencySession->default_currency) and $this->currencySession->default_currency) {
            return ($currencySession ? $this->currencySession : $this->currencySession->default_currency);
        } else {
            $currencyArray = $this->getFrontCurrency();
            foreach ($currencyArray as $c_value) {
                if($c_value['currency_type'] == '1') {
                    $this->currencySession->default_currency        = $c_value['currency_code'];
                    $this->currencySession->default_currency_name   = $c_value['currency_name'];
                    $this->currencySession->default_currency_rate   = $c_value['currency_rate'];
                    $this->currencySession->default_currency_symbol = $c_value['currency_symbol'];
                    $this->currencySession->default_currency_unit   = (!empty($c_value['currency_unit']) ? '&nbsp;' : '').$c_value['currency_unit'];
                    $this->currencySession->default_currency_decimal= $c_value['currency_decimal'];
                }
            }
            return ($currencySession ? $this->currencySession : $this->currencySession->default_currency);
        }
    }

    /**
     * 设置默认货币信息
     * @param $currencyCode
     */
    public function setFrontDefaultCurrency($currencyCode)
    {
        $array = $this->getFrontCurrency();
        if(!empty($currencyCode) and $array[$currencyCode] != '') {
            $this->currencySession->default_currency        = $currencyCode;
            $this->currencySession->default_currency_name   = $array[$currencyCode]['currency_name'];
            $this->currencySession->default_currency_rate   = $array[$currencyCode]['currency_rate'];
            $this->currencySession->default_currency_symbol = $array[$currencyCode]['currency_symbol'];
            $this->currencySession->default_currency_unit   = (!empty($c_value['currency_unit']) ? '&nbsp;' : '').$array[$currencyCode]['currency_unit'];
            $this->currencySession->default_currency_decimal= $array[$currencyCode]['currency_decimal'];
        }
    }
    /*-----------------------------------货币信息----------------------------------------*/
    
    /*-----------------------------------导航信息----------------------------------------*/
    /**
     * 获取导航信息
     * @param $type
     * @return array|mixed
     */
    public function shopFrontMenu($type)
    {
        $array = array();
        $file = DBSHOP_PATH . '/data/moduledata/Navigation/' . $type . '.php';
        if(file_exists($file)) {
            return include $file;
        }
        return $array;
    }
    /*-----------------------------------货币信息----------------------------------------*/
    
    /*-----------------------------------商品价格转换----------------------------------------*/
    /**
     * 带前后缀的价格
     * @param $price
     * @return string
     */
    public function shopPriceExtend($price)
    {
        if($price == 0) return 0;

        if(!isset($this->currencySession->default_currency)) $this->getFrontDefaultCurrency();

        //必须会员登录可见价格
        //if(!isset($this->userSession->user_id) and $this->getDbshopGoodsIni('dbshop_login_goods_price_show') == 1) return '<a href="'.$this->getView()->url('frontuser/default',array('action'=>'login')).'" style="color:#ed6343;">'.$this->getDbshopGoodsIni('dbshop_login_goods_price_word').'</a>';
        if(!isset($this->userSession->user_id) and $this->getDbshopGoodsIni('dbshop_login_goods_price_show') == 1) return $this->getDbshopGoodsIni('dbshop_login_goods_price_word');

        //return $this->currencySession->default_currency_symbol . floatval(number_format($price * $this->currencySession->default_currency_rate, $this->currencySession->default_currency_decimal, '.', '')) . $this->currencySession->default_currency_unit;
        return $this->currencySession->default_currency_symbol . number_format($price * $this->currencySession->default_currency_rate, $this->currencySession->default_currency_decimal, '.', '') . $this->currencySession->default_currency_unit;
    }
    /**
     * api不带前后缀的价格（判断是否为登录用户）
     * @param $price
     * @return string
     */
    public function apiShopPrice($price, $array)
    {
        if($price == 0) return 0;

        if(!isset($this->currencySession->default_currency)) $this->getFrontDefaultCurrency();

        //必须会员登录可见价格
        if((!isset($array['user_id']) || (isset($array['user_id']) and $array['user_id'] <= 0)) and $this->getDbshopGoodsIni('dbshop_login_goods_price_show') == 1) return $this->getDbshopGoodsIni('dbshop_login_goods_price_word');

        return number_format($price * $this->currencySession->default_currency_rate, $this->currencySession->default_currency_decimal, '.', '');
    }

    /**
     * api里直接显示价格
     * @param $price
     * @return string
     */
    public function apiPrice($price)
    {
        if($price == 0) return 0;

        if(!isset($this->currencySession->default_currency)) $this->getFrontDefaultCurrency();
        return number_format($price * $this->currencySession->default_currency_rate, $this->currencySession->default_currency_decimal, '.', '');
    }
    /**
     * 不带前后缀的价格
     * @param $price
     * @return string
     */
    public function shopPrice($price)
    {
        if($price == 0) return 0;

        if(!isset($this->currencySession->default_currency)) $this->getFrontDefaultCurrency();

        //必须会员登录可见价格
        //if(!isset($this->userSession->user_id) and $this->getDbshopGoodsIni('dbshop_login_goods_price_show') == 1) return '<a href="'.$this->getView()->url('frontuser/default',array('action'=>'login')).'" style="color:#ed6343;">'.$this->getDbshopGoodsIni('dbshop_login_goods_price_word').'</a>';
        if(!isset($this->userSession->user_id) and $this->getDbshopGoodsIni('dbshop_login_goods_price_show') == 1) return $this->getDbshopGoodsIni('dbshop_login_goods_price_word');

        //return floatval(number_format($price * $this->currencySession->default_currency_rate, $this->currencySession->default_currency_decimal, '.', ''));
        return number_format($price * $this->currencySession->default_currency_rate, $this->currencySession->default_currency_decimal, '.', '');
    }
    /**
     * 输入价格和货币code获取对应货币转换价格
     * @param $price
     * @param $currencyCode
     * @return string
     */
    public function currencyPrice($price, $currencyCode)
    {
        if($price == 0) return 0;

        $currencyArray = $this->getFrontCurrency();
        if(!isset($currencyArray[$currencyCode]) or empty($currencyArray[$currencyCode])) return $price;

        //return floatval(number_format($price * $currencyArray[$currencyCode]['currency_rate'], $currencyArray[$currencyCode]['currency_decimal'], '.', ''));
        return number_format($price * $currencyArray[$currencyCode]['currency_rate'], $currencyArray[$currencyCode]['currency_decimal'], '.', '');
    }
    /**
     * 货币
     * @return mixed
     */
    public function shopCurrency()
    {
        if(!isset($this->currencySession->default_currency)) $this->getFrontDefaultCurrency();
        return $this->currencySession->default_currency;
    }
    /**
     * 货币符号
     * @return mixed
     */
    public function shopPriceSymbol()
    {
        if(!isset($this->currencySession->default_currency)) $this->getFrontDefaultCurrency();
        return $this->currencySession->default_currency_symbol;
    }
    /**
     * 货币汇率
     * @return mixed
     */
    public function shopPriceRate()
    {
        if(!isset($this->currencySession->default_currency)) $this->getFrontDefaultCurrency();
        return $this->currencySession->default_currency_rate;
    }
    /**
     * 货币单位
     * @return mixed
     */
    public function shopPriceUnit()
    {
        if(!isset($this->currencySession->default_currency)) $this->getFrontDefaultCurrency();
        return $this->currencySession->default_currency_unit;
    }
    /*-----------------------------------商品价格转换----------------------------------------*/
    /**
     * 商品图片处理，当为空时显示默认图片(前台商品图片显示)，前台有cdn处理，所以后台单独拿出来
     * @param $goodsImage
     * @return mixed
     */
    public function shopGoodsImage($goodsImage)
    {
        $image = $goodsImage;
        $qiniuHttp  = (isset($this->storageConfig['qiniu_http_type']) ? $this->storageConfig['qiniu_http_type'] : 'http://');
        $aliyunHttp = (isset($this->storageConfig['aliyun_http_type']) ? $this->storageConfig['aliyun_http_type'] : 'http://');

        if(stripos($image, '{qiniu}') !== false) return str_replace('{qiniu}', $qiniuHttp.$this->storageConfig['qiniu_domain'], $image);
        if(stripos($image, '{aliyun}') !== false) return str_replace('{aliyun}', $aliyunHttp.$this->storageConfig['aliyun_domain'], $image);
        if(defined('FRONT_CDN_STATE') and FRONT_CDN_STATE == 'true') {//开启cdn图片加速
            //if(stripos($image, 'http') === false) return FRONT_CDN_HTTP_TYPE . FRONT_CDN_DOMAIN . '/goods/' . basename($image);
            if(stripos($image, 'http') === false) {
                $dbshopPath = $_SERVER['PHP_SELF'] ? dirname($_SERVER['PHP_SELF']) : dirname($_SERVER['SCRIPT_NAME']);
                $dbshopPath = ($dbshopPath == '/' ? '' : $dbshopPath);

                if($image == '' or !file_exists(DBSHOP_PATH . $image)) $image = $this->getGoodsUploadIni('goods', 'goods_image_default');

                return FRONT_CDN_HTTP_TYPE . FRONT_CDN_DOMAIN . $dbshopPath . $image;
            }
        }

        if($image == '' or !file_exists(DBSHOP_PATH . $image)) $image = $this->getGoodsUploadIni('goods', 'goods_image_default');
        return $image;
    }
    /**
     * 商品图片处理，当为空时显示默认图片(后台商品图片显示)
     * @param $goodsImage
     * @return mixed
     */
    public function shopadminGoodsImage($goodsImage) {
        $image = $goodsImage;
        $qiniuHttp  = (isset($this->storageConfig['qiniu_http_type']) ? $this->storageConfig['qiniu_http_type'] : 'http://');
        $aliyunHttp = (isset($this->storageConfig['aliyun_http_type']) ? $this->storageConfig['aliyun_http_type'] : 'http://');

        if(stripos($image, '{qiniu}') !== false) return str_replace('{qiniu}', $qiniuHttp.$this->storageConfig['qiniu_domain'], $image);
        if(stripos($image, '{aliyun}') !== false) return str_replace('{aliyun}', $aliyunHttp.$this->storageConfig['aliyun_domain'], $image);

        if($image == '' or !file_exists(DBSHOP_PATH . $image)) $image = $this->getGoodsUploadIni('goods', 'goods_image_default');
        return $image;
    }
    /**
     * 对商品详情进行处理
     * @param $goodsBody
     */
    /**
     * 对商品详情进行处理
     * @param $goodsBody
     * @return mixed
     */
    public function shopGoogsBody($goodsBody)
    {
        if(defined('FRONT_CDN_STATE') and FRONT_CDN_STATE == 'true') {//开启cdn图片加速
            $imageBaseUrl = FRONT_CDN_HTTP_TYPE . FRONT_CDN_DOMAIN;

            if($this->isMobile()) {
                $cdnImageStyle = (defined('FRONT_CDN_IMAGE_PHONE_STYLE')   ? FRONT_CDN_IMAGE_PHONE_STYLE   : '');
            } else {
                $cdnImageStyle = (defined('FRONT_CDN_IMAGE_PC_STYLE')      ? FRONT_CDN_IMAGE_PC_STYLE      : '');
            }

            preg_match_all('/<img(.*)src="([^"]+)"[^>]+>/isU', $goodsBody, $matches);
            if(isset($matches[2]) and !empty($matches[2])) {
                $images         = $matches[2];
                $patterns       = array();
                $replacements   = array();
                foreach($images as $imageitem) {
                    if(stripos($imageitem, 'http') === false) {
                        //$replacements[] = $imageBaseUrl . '/goods/'. basename($imageitem);
                        $replacements[] = $imageBaseUrl . $imageitem . (!empty($cdnImageStyle) ? $cdnImageStyle : '');
                        $patterns[]     = "/".preg_replace("/\//i","\/",$imageitem)."/";
                    }
                }
                if(!empty($replacements)) {
                    ksort($patterns);
                    ksort($replacements);
                    $goodsBody = preg_replace($patterns, $replacements, $goodsBody);
                }
            }
        } else {
            if($this->isMobile()) {
                $localImageStyle = isset($this->storageConfig['local_phone_image_width']) ? $this->storageConfig['local_phone_image_width'] : '';
                $qinuiImageStyle = isset($this->storageConfig['qiniu_phone_image_style']) ? $this->storageConfig['qiniu_phone_image_style'] : '';
                $aliyunImageStyle= isset($this->storageConfig['aliyun_phone_image_style']) ? $this->storageConfig['aliyun_phone_image_style'] : '';
                $localImagePath = '../../../dbgoodsimage';
            } else {
                $localImageStyle = isset($this->storageConfig['local_pc_image_width']) ? $this->storageConfig['local_pc_image_width'] : '';
                $qinuiImageStyle = isset($this->storageConfig['qiniu_pc_image_style']) ? $this->storageConfig['qiniu_pc_image_style'] : '';
                $aliyunImageStyle= isset($this->storageConfig['aliyun_pc_image_style']) ? $this->storageConfig['aliyun_pc_image_style'] : '';
                $localImagePath = '../../dbgoodsimage';
            }

            if(!empty($localImageStyle) or !empty($qinuiImageStyle) or !empty($aliyunImageStyle)) {
                $localImageModule = false;
                if(isset($GLOBALS['extendModule']['modules']) and !empty($GLOBALS['extendModule']['modules']) and in_array('Dbgoodsimage', $GLOBALS['extendModule']['modules'])) $localImageModule = true;

                preg_match_all('/<img(.*)src="([^"]+)"[^>]+>/isU', $goodsBody, $matches);
                if(isset($matches[2]) and !empty($matches[2])) {
                    include_once DBSHOP_PATH . '/module/Upload/src/Upload/Plugin/Thumb/ThumbLib.inc.php';

                    if(!empty($localImageStyle) and $localImageModule) {
                        include_once DBSHOP_PATH . '/module/Extendapp/Dbgoodsimage/src/Dbgoodsimage/phpThumb/phpThumb.config.php';
                    }

                    $images         = $matches[2];
                    $patterns       = array();
                    $replacements   = array();
                    foreach($images as $imageitem) {
                        if(!empty($aliyunImageStyle) and stripos($imageitem, $this->storageConfig['aliyun_domain']) !== false ) {
                            $replacements[] = $imageitem . $aliyunImageStyle;
                            $patterns[]     = "/".preg_replace("/\//i","\/",$imageitem)."/";
                        }
                        if(!empty($qinuiImageStyle) and stripos($imageitem, $this->storageConfig['qiniu_domain']) !== false ) {
                            $replacements[] = $imageitem . $qinuiImageStyle;
                            $patterns[]     = "/".preg_replace("/\//i","\/",$imageitem)."/";
                        }
                        if(!empty($localImageStyle) and stripos($imageitem, 'http') === false and $localImageModule) {
                            $replacements[] = htmlspecialchars(phpThumbURL('src=' . $imageitem . '&w='.$localImageStyle, $localImagePath), ENT_QUOTES);
                            $patterns[]     = "/".preg_replace("/\//i","\/",$imageitem)."/";
                        }
                    }
                    if(!empty($replacements)) {
                        ksort($patterns);
                        ksort($replacements);
                        $goodsBody = preg_replace($patterns, $replacements, $goodsBody);
                    }
                }
            }
        }

        return $goodsBody;
    }
    /**
     * 获取广告信息
     * @param $adClass
     * @param $adPlace
     * @param string $showType
     * @return null|string
     */
    public function getShopAd($adClass, $adPlace, $showType='pc', $class_id='')
    {
        $adContent = '';
        if($showType == 'pc') {
            $filePath  = DBSHOP_PATH . '/data/moduledata/Ad/' . DBSHOP_TEMPLATE . '/';
        } else {
            $filePath  = DBSHOP_PATH . '/data/moduledata/Ad/mobile/' . (defined('DBSHOP_PHONE_TEMPLATE') ? DBSHOP_PHONE_TEMPLATE : 'default') . '/';
        }

        if(!file_exists($filePath . $adClass . '.ini')) return null;
        
        $adArray   = $this->iniReader->fromFile($filePath . $adClass . '.ini');
        if(isset($adArray[$adPlace]) and is_array($adArray[$adPlace]) and !empty($adArray[$adPlace])) {
            foreach ($adArray[$adPlace] as $value) {
                if($value['state'] == 1 and file_exists($filePath . $value['file'])) {
                    $startTimeState = (isset($value['start_time']) and !empty($value['start_time'])) ? true : false;
                    $endTimeState   = (isset($value['end_time']) and !empty($value['end_time'])) ? true : false;
                    //判断是否是商品分类或者商品详情页面的广告，是否设置的指定广告
                    if(isset($class_id) and $class_id > 0 and isset($value['goods_class_id']) and !empty($value['goods_class_id'])) {
                        $goodsClassIdArray = explode(',', $value['goods_class_id']);
                        if(!in_array($class_id, $goodsClassIdArray)) continue;
                    }

                    if($startTimeState and $endTimeState and time()>=$value['start_time'] and time()<=$value['end_time']) $adContent .= file_get_contents($filePath . $value['file']);
                    if(!$startTimeState and $endTimeState and time()<=$value['end_time']) $adContent .= file_get_contents($filePath . $value['file']);
                    if($startTimeState and !$endTimeState and time()>=$value['start_time']) $adContent .= file_get_contents($filePath . $value['file']);
                    if(!$startTimeState and !$endTimeState) $adContent .= file_get_contents($filePath . $value['file']);
                }
            }
        }
        return $adContent;
    }
    /*-----------------------------------消息提醒----------------------------------------*/
    /**
     * 获取消息提醒是否发给管理员
     * @param $stateName
     * @return string
     */
    public function getSendMessageAdminEmail($stateName)
    {
        $messageSet = $this->iniReader->fromFile(DBSHOP_PATH . '/data/moduledata/System/sendmessage/sendmessage.ini');
        if(isset($messageSet[$stateName]) and $messageSet[$stateName] == '1') {
            return $messageSet['admin_receive_email'];
        }
        return '';
    }
    /**
     * 获取消息提醒是否发给买家
     * @param $stateName
     * @param $buyerEmail
     * @return string
     */
    public function getSendMessageBuyerEmail($stateName, $buyerEmail='')
    {
        if(empty($buyerEmail)) return '';

        $messageSet = $this->iniReader->fromFile(DBSHOP_PATH . '/data/moduledata/System/sendmessage/sendmessage.ini');
        if(isset($messageSet['buyer_'.$stateName]) and $messageSet['buyer_'.$stateName] == '1') {
            return $buyerEmail;
        }
        return '';
    }
    /**
     * 获取消息模板内容
     * @param $fileName
     * @return string
     */
    public function getSendMessageBody($fileName)
    {
        $content = @file_get_contents(DBSHOP_PATH . '/data/moduledata/System/sendmessage/' . $fileName . '.php');
        return $content;
    }
    /**
     * 生成消息内容
     * @param $data
     * @param $messageContent
     * @return string
     */
    public function createSendMessageContent($data, $messageContent)
    {
        $sourceArray = array(
                '{shopname}',       //网站名称
                '{buyname}',        //买家名称
                '{ordersn}',        //订单编号
                '{ordertotal}',     //订单金额
                '{expressname}',    //快递公司
                '{expressnumber}',  //快递单号
                '{submittime}',     //订单提交时间
                '{shopurl}',        //网站url
                '{paymenttime}',    //支付完成时间
                '{shiptime}',       //订单发货时间
                '{finishtime}',     //订单完成时间
                '{canceltime}',     //订单取消时间
                '{cancelinfo}',     //订单取消原因
                '{deltime}',        //订单删除时间

                '{askusername}',    //商品咨询者名称
                '{goodsname}',      //商品名称
                '{asktime}',        //商品咨询时间
                '{replyusername}',  //回复者名称
                '{replytime}',      //回复时间

                '{goodsname}',      //商品名称
                '{goodsstock}',     //商品库存
                '{virtualaccount}', //虚拟商品账号
                '{virtualpassword}' //虚拟商品密码
        );
        $bodyArray  = array(
                '{shopname}'   => (isset($data['shopname'])     ? $data['shopname']     : ''),
                '{buyname}'    => (isset($data['buyname'])      ? $data['buyname']      : ''),
                '{ordersn}'    => (isset($data['ordersn'])      ? $data['ordersn']      : ''),
                '{ordertotal}' => (isset($data['ordertotal'])      ? $data['ordertotal']      : ''),
                '{expressname}'=> (isset($data['expressname'])      ? $data['expressname']    : ''),
                '{expressnumber}' => (isset($data['expressnumber']) ? $data['expressnumber']  : ''),
                '{submittime}' => (isset($data['submittime'])   ? date("Y-m-d H:i:s", $data['submittime'])   : ''),
                '{shopurl}'    => (isset($data['shopurl'])      ? '<a href="'. $data['shopurl'] . '" target="_blank">' . $data['shopurl'] . '</a>'     : ''),
                '{paymenttime}'=> (isset($data['paymenttime'])  ? date("Y-m-d H:i:s", $data['paymenttime'])  : ''),
                '{shiptime}'   => (isset($data['shiptime'])     ? date("Y-m-d H:i:s", $data['shiptime'])     : ''),
                '{finishtime}' => (isset($data['finishtime'])   ? date("Y-m-d H:i:s", $data['finishtime'])   : ''),
                '{canceltime}' => (isset($data['canceltime'])   ? date("Y-m-d H:i:s", $data['canceltime'])   : ''),
                '{cancelinfo}' => (isset($data['cancel_info'])  ? trim($data['cancel_info'])                 : ''),
                '{deltime}'    => (isset($data['deltime'])      ? date("Y-m-d H:i:s", $data['deltime'])      : ''),

                '{askusername}'    => (isset($data['askusername'])   ? $data['askusername']                  : ''),
                '{goodsname}'      => (isset($data['goodsname'])     ? $data['goodsname']                    : ''),
                '{asktime}'        => (isset($data['asktime'])       ? date("Y-m-d H:i:s", $data['asktime']) : ''),
                '{replyusername}'  => (isset($data['replyusername']) ? $data['replyusername']                : ''),
                '{replytime}'      => (isset($data['replytime'])     ? date("Y-m-d H:i:s", $data['replytime']): ''),

                '{goodsname}'      => (isset($data['goodsname'])        ? $data['goodsname']        : ''),
                '{goodsstock}'     => (isset($data['goodsstock'])       ? $data['goodsstock']       : ''),
                '{virtualaccount}' => (isset($data['virtualaccount'])   ? $data['virtualaccount']   : ''),
                '{virtualpassword}'=> (isset($data['virtualpassword'])  ? $data['virtualpassword']  : ''),
        );
        //将邮件内容，进行html化处理，防止没有正常显示html格式问题
        $messageContent = htmlspecialchars($messageContent);
        $messageContent = str_replace("\n", "<br>", $messageContent);
        $messageContent = str_replace("  ", "&nbsp;", $messageContent);

        foreach ($sourceArray as $value) {
            $messageContent = str_replace($value, $bodyArray[$value], $messageContent);
        }

        return $messageContent;
    }
    /*-----------------------------------消息提醒----------------------------------------*/

    /*-----------------------------------手机消息提醒----------------------------------------*/
    /**
     * 获取手机短信的配置信息
     * @param $type
     * @param $sign
     * @return string
     */
    public function getIphoneSmsConfig($type, $sign) {
        $config = $this->iniReader->fromFile(DBSHOP_PATH . '/data/moduledata/System/phonesms.ini');
        if(isset($config[$type][$sign])) return $config[$type][$sign];

        return '';
    }
    /*-----------------------------------手机消息提醒----------------------------------------*/

    /**
     * 获取特殊标签对应商品信息
     * @param $tagCode
     * @param int $goodsNum
     * @param string $tagType
     * @return array|null
     */
    public function getTagGoodsArray($tagCode, $goodsNum=0, $tagType='pc')
    {
        $goodsNum = intval($goodsNum);
        
        if(empty($this->dbshopSql)) {
            $this->dbshopSql = new \Zend\Db\Adapter\Adapter(include DBSHOP_PATH . '/data/Database.ini.php');
        }
        if(empty($this->dbshopResultSet)) {
            $this->dbshopResultSet = new \Zend\Db\ResultSet\ResultSet();
        }
        if($tagType == 'pc') {
            $dbshopTemplate = DBSHOP_TEMPLATE;
        } else {
            $dbshopTemplate = DBSHOP_PHONE_TEMPLATE;
        }
        $query  = $this->dbshopSql->query('SELECT tag_id FROM dbshop_goods_tag WHERE tag_type=\''.$tagCode.'\' and template_tag=\''.$dbshopTemplate.'\' and show_type=\''.$tagType.'\'');
        $result = $query->execute()->current();
        if(isset($result['tag_id'])) {
            $userGroupId = $this->getUserSession('group_id');
            $subSql = '';
            if($userGroupId > 0) {
                $subSql = ',(SELECT gp.goods_user_group_price FROM dbshop_goods_usergroup_price as gp WHERE gp.goods_id=g.goods_id and gp.user_group_id='.$userGroupId.' and gp.goods_color=\'\' and gp.goods_size=\'\' and gp.adv_spec_tag_id=\'\') as group_price';
            }

            $selectSql = "SELECT g.*, e.goods_name, e.goods_extend_name,
                    (SELECT i.goods_thumbnail_image FROM dbshop_goods_image as i WHERE i.goods_image_id=e.goods_image_id) as goods_thumbnail_image,
                    (SELECT in_c.class_id FROM dbshop_goods_in_class as in_c WHERE in_c.goods_id=g.goods_id and in_c.class_state=1 LIMIT 1) as one_class_id,
                    (SELECT SUM(og.buy_num) FROM dbshop_order_goods AS og INNER JOIN dbshop_order as do ON do.order_id=og.order_id WHERE og.goods_id=g.goods_id and do.order_state!=0) AS buy_num,
                    (SELECT count(uf.favorites_id) FROM dbshop_user_favorites AS uf WHERE uf.goods_id=g.goods_id) AS favorites_num
                    ".$subSql."
                    FROM dbshop_goods as g
                    INNER JOIN dbshop_goods_extend as e ON e.goods_id=g.goods_id
                    INNER JOIN dbshop_goods_tag_in_goods as t ON t.goods_id=g.goods_id";
            $whereSql  = " WHERE t.tag_id=".$result['tag_id']." and g.goods_state=1";
            $orderSql  = " order by t.tag_goods_sort ASC, t.goods_id DESC";
            $limitSql  = " " . ($goodsNum > 0 ? 'limit '.$goodsNum : '');

            $query     = $this->dbshopSql->query($selectSql . $whereSql . $orderSql . $limitSql);
            $goodsArray= $this->dbshopResultSet->initialize($query->execute())->toArray();

            if(is_array($goodsArray) and !empty($goodsArray)) {
                foreach($goodsArray as $key => $value) {
                    if(isset($value['group_price']) and $value['group_price'] > 0) {
                        $value['goods_shop_price'] = $value['group_price'];
                        $goodsArray[$key] = $value;
                    }
                }
            }

            return $goodsArray;
        }
        return null;
    }
    /**
     * 特殊标签对应的文章，可以是单页模式，也可以是文章分类中的文章模式
     * @param $tagCode
     * @param string $type single为单页模式
     * @param string $limitNum
     * @param string $templateType
     * @return array
     */
    public function getTagArticleArray($tagCode, $type='single', $limitNum='', $templateType='pc')
    {
        if(empty($this->dbshopSql)) {
            $this->dbshopSql = new \Zend\Db\Adapter\Adapter( include DBSHOP_PATH . '/data/Database.ini.php');
        }
        if(empty($this->dbshopResultSet)) {
            $this->dbshopResultSet = new \Zend\Db\ResultSet\ResultSet();
        }
        if($type == 'single') {//单页模式获取
            if($templateType == 'pc') {
                $selectSql    = "SELECT * FROM dbshop_single_article as ds INNER JOIN dbshop_single_article_extend as e ON e.single_article_id=ds.single_article_id WHERE ds.article_tag='".$tagCode."' and ds.template_tag='".DBSHOP_TEMPLATE."'";
            } else {
                $selectSql    = "SELECT * FROM dbshop_single_article as ds INNER JOIN dbshop_single_article_extend as e ON e.single_article_id=ds.single_article_id WHERE ds.article_tag='".$tagCode."' and ds.template_tag='".DBSHOP_PHONE_TEMPLATE."'";
            }
        }
        if($type == 'cms') {//文章分类中的文章模式(新闻)
            $selectSql = "SELECT article_class_id FROM dbshop_article_class WHERE index_news=1 and article_class_state=1 limit 1";
            $query        = $this->dbshopSql->query($selectSql);
            $array        = $this->dbshopResultSet->initialize($query->execute())->toArray();
            $selectSql = '';
            if(isset($array[0]['article_class_id'])) {
                $where = 'WHERE a.article_state=1 ';
                if($tagCode == 'index_news') $where .= 'and a.article_class_id IN (SELECT c.article_class_id FROM dbshop_article_class as c WHERE (c.article_class_id='.$array[0]['article_class_id'].' or c.article_class_top_id='.$array[0]['article_class_id'].') and c.article_class_state=1)';
                if(!empty($limitNum)) $limit = ' limit '.$limitNum;
                $selectSql   = "SELECT * FROM dbshop_article as a
                            INNER JOIN dbshop_article_extend as e ON e.article_id=a.article_id
                            ".$where." ORDER BY a.article_sort ASC,a.article_id DESC ".$limit;
            }
        }

        $articleArray = array();
        if(!empty($selectSql)) {
            $query        = $this->dbshopSql->query($selectSql);
            $articleArray = $this->dbshopResultSet->initialize($query->execute())->toArray();
        }
        
        return $articleArray;
    }
    /*-----------------------------------商品设置----------------------------------------*/
    /**
     * 商品设置ini信息
     * @param $sign
     * @param string $type
     * @return string
     */
    public function getDbshopGoodsIni($sign, $type='shop_goods')
    {
        if(empty($this->goodsConfig)) {
            $this->goodsConfig = $this->iniReader->fromFile(DBSHOP_PATH . '/data/moduledata/System/goods/goods.ini');
        }
        if(!empty($type) and isset($this->goodsConfig[$type][$sign])) return $this->goodsConfig[$type][$sign];
        elseif(isset($this->goodsConfig[$sign])) return $this->goodsConfig[$sign];

        return '';
    }
    /**
     * 商品分享代码输出
     * @return array
     */
    public function getDbshopGoodsShare()
    {
        if(empty($this->goodsConfig)) {
            $this->goodsConfig = $this->iniReader->fromFile(DBSHOP_PATH . '/data/moduledata/System/goods/goods.ini');
        }
        $shareCode = (!empty($this->goodsConfig['shop_goods']['dbshop_goods_share']) ? @file_get_contents(DBSHOP_PATH . '/data/moduledata/System/goods/share' . $this->goodsConfig['shop_goods']['dbshop_goods_share']) : '');

        return array('share_type'=>$this->goodsConfig['shop_goods']['dbshop_goods_share'], 'share_code'=>$shareCode);
    }
    /*-----------------------------------商品设置----------------------------------------*/
    /**
     * 二维码生成
     * @param $text
     * @param $name
     * @param string $type
     * @return string
     */
    public function createQRcode($text, $name, $type='shop')
    {
        $qrCodePath = '/public';
        $fileName   = '';
        if($type == 'shop'){
            $qrCodePath  = $qrCodePath . '/img/shopqrcode/';
            $fileName    = md5($name) . '.png';
        }
        if($type == 'goods') {
            $qrCodePath = $qrCodePath . '/upload/goods/qrcode/';
            $fileName   = intval($name) . '.png';
        }
        if($type == 'invitation') {
            $qrCodePath = $qrCodePath . '/upload/user/';
            $fileName   = 'invitation_' . intval($name) . '.png';
            if(file_exists(DBSHOP_PATH . $qrCodePath . $fileName)) @unlink(DBSHOP_PATH . $qrCodePath . $fileName);
        }
        if(!is_dir(DBSHOP_PATH . $qrCodePath)) @mkdir(DBSHOP_PATH . $qrCodePath, 0755, true);

        if(!file_exists(DBSHOP_PATH . $qrCodePath . $fileName)) {
            include DBSHOP_PATH . '/module/Upload/src/Upload/Plugin/Phpqrcode/phpqrcode.php';
            \QRcode::png($text, DBSHOP_PATH . $qrCodePath . $fileName, QR_ECLEVEL_L, 6, 1);
        }

        return $qrCodePath . $fileName;
    }
    /**
     * 获取经过处理后的密码
     * @param $password
     * @return string
     */
    public function getPasswordStr($password)
    {
        $keyCode = '?3b)f*ixoY!WQ4t{jyk#<{/HZXIw$>7Kr?+VN`?tN8qRJZ?6GW|oJW|{z+KBe2@?';
        return md5($keyCode . trim($password));
    }
    /**
     * 检查手机号码的正确性
     * @param $phone
     * @return string
     */
    public function checkPhoneNum($phone)
    {
        $phoneState = preg_match('#^13[\d]{9}$|^14[\d]{9}$|^15[\d]{9}$|^16[\d]{9}$|^17[\d]{9}$|^18[\d]{9}$|^19[\d]{9}$#', $phone) ? 'true' : 'false';
        return $phoneState;
    }
    /*-----------------------------------判断是否为手机端----------------------------------------*/
    public function isMobile()
    {
        $useragent=isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $useragent_commentsblock=preg_match('|\(.*?\)|',$useragent,$matches)>0?$matches[0]:'';

        $mobile_os_list=array('Google Wireless Transcoder','Windows CE','WindowsCE','Symbian','Android','armv6l','armv5','Mobile','CentOS','mowser','AvantGo','Opera Mobi','J2ME/MIDP','Smartphone','Go.Web','Palm','iPAQ');
        $mobile_token_list=array('Profile/MIDP','Configuration/CLDC-','160×160','176×220','240×240','240×320','320×240','UP.Browser','UP.Link','SymbianOS','PalmOS','PocketPC','SonyEricsson','Nokia','BlackBerry','Vodafone','BenQ','Novarra-Vision','Iris','NetFront','HTC_','Xda_','SAMSUNG-SGH','Wapaka','DoCoMo','iPhone','iPod');

        $found_mobile=$this->CheckSubstrs($mobile_os_list,$useragent_commentsblock) || $this->CheckSubstrs($mobile_token_list,$useragent);

        if ($found_mobile){
            return true;
        }else{
            return false;
        }

        /*if ($this->isMobile())
            echo '手机登录';
        else
            echo '电脑登录';*/
    }
    public function CheckSubstrs($substrs,$text){
        foreach($substrs as $substr)
            if(false!==strpos($text,$substr)){
                return true;
            }
        return false;
    }

    /**
     * 分享图片生成
     * @param $gData  商品数据，array
     * @param $codeName 二维码图片
     * @param $fileName string 保存文件名,默认空则直接输入图片
     */
    public function createSharePng($gData,$codeName,$fileName = '', $language){
        //创建画布
        $im = imagecreatetruecolor(618, 890);

        //填充画布背景色
        $color = imagecolorallocate($im, 255, 255, 255);
        imagefill($im, 0, 0, $color);

        //字体文件
        $font_file = DBSHOP_PATH . "/public/img/simfang.ttf";

        //设定字体的颜色
        $font_color_1 = ImageColorAllocate ($im, 140, 140, 140);
        $font_color_2 = ImageColorAllocate ($im, 28, 28, 28);
        $font_color_3 = ImageColorAllocate ($im, 129, 129, 129);
        $font_color_red = ImageColorAllocate ($im, 217, 45, 32);

        $fang_bg_color = ImageColorAllocate ($im, 254, 216, 217);

        //温馨提示
        imagettftext($im, 14,0, 70, 860, $font_color_1 ,$font_file, $language['info']);

        //商品图片
        list($g_w,$g_h) = getimagesize($gData['pic']);
        $goodImg = $this->createImageFromFile($gData['pic']);
        imagecopyresized($im, $goodImg, 0, 0, 0, 0, 618, 618, $g_w, $g_h);

        //二维码
        list($code_w,$code_h) = getimagesize($codeName);
        $codeImg = $this->createImageFromFile($codeName);
        imagecopyresized($im, $codeImg, 440, 650, 0, 0, 170, 170, $code_w, $code_h);

        //商品描述
        $theTitle = $this->cnRowSubstr($gData['title'],2,19);
        imagettftext($im, 14,0, 8, 670, $font_color_2 ,$font_file, $theTitle[1]);
        imagettftext($im, 14,0, 8, 700, $font_color_2 ,$font_file, $theTitle[2]);

        imagettftext($im, 16,0, 8, 745, $font_color_2 ,$font_file, $language['price']);
        imagettftext($im, 25,0, 60, 745, $font_color_red ,$font_file, $gData["price"]);

        //输出图片
        if($fileName){
            imagepng ($im,$fileName);
        }else{
            Header("Content-Type: image/png");
            imagepng ($im);
        }

        //释放空间
        imagedestroy($im);
        imagedestroy($goodImg);
        imagedestroy($codeImg);
    }

    /**
     * 从图片文件创建Image资源
     * @param $file 图片文件，支持url
     * @return bool|resource    成功返回图片image资源，失败返回false
     */
    private function createImageFromFile($file){
        if(preg_match('/http(s)?:\/\//',$file)){
            $fileSuffix = $this->getNetworkImgType($file);
        }else{
            $fileSuffix = pathinfo($file, PATHINFO_EXTENSION);
        }

        if(!$fileSuffix) return false;

        switch ($fileSuffix){
            case 'jpeg':
                $theImage = @imagecreatefromjpeg($file);
                break;
            case 'jpg':
                $theImage = @imagecreatefromjpeg($file);
                break;
            case 'png':
                $theImage = @imagecreatefrompng($file);
                break;
            case 'gif':
                $theImage = @imagecreatefromgif($file);
                break;
            default:
                $theImage = @imagecreatefromstring(file_get_contents($file));
                break;
        }

        return $theImage;
    }

    /**
     * 获取网络图片类型
     * @param $url  网络图片url,支持不带后缀名url
     * @return bool
     */
    private function getNetworkImgType($url){
        $ch = curl_init(); //初始化curl
        curl_setopt($ch, CURLOPT_URL, $url); //设置需要获取的URL
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);//设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //支持https
        curl_exec($ch);//执行curl会话
        $http_code = curl_getinfo($ch);//获取curl连接资源句柄信息
        curl_close($ch);//关闭资源连接

        if ($http_code['http_code'] == 200) {
            $theImgType = explode('/',$http_code['content_type']);

            if($theImgType[0] == 'image'){
                return $theImgType[1];
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    /**
     * 分行连续截取字符串
     * @param $str  需要截取的字符串,UTF-8
     * @param int $row  截取的行数
     * @param int $number   每行截取的字数，中文长度
     * @param bool $suffix  最后行是否添加‘...’后缀
     * @return array    返回数组共$row个元素，下标1到$row
     */
    private function cnRowSubstr($str,$row = 1,$number = 10,$suffix = true){
        $result = array();
        for ($r=1;$r<=$row;$r++){
            $result[$r] = '';
        }

        $str = trim($str);
        if(!$str) return $result;

        $theStrlen = strlen($str);

        //每行实际字节长度
        $oneRowNum = $number * 3;
        for($r=1;$r<=$row;$r++){
            if($r == $row and $theStrlen > $r * $oneRowNum and $suffix){
                $result[$r] = $this->mgCnSubstr($str,$oneRowNum-6,($r-1)* $oneRowNum).'...';
            }else{
                $result[$r] = $this->mgCnSubstr($str,$oneRowNum,($r-1)* $oneRowNum);
            }
            if($theStrlen < $r * $oneRowNum) break;
        }

        return $result;
    }

    /**
     * 按字节截取utf-8字符串
     * 识别汉字全角符号，全角中文3个字节，半角英文1个字节
     * @param $str  需要切取的字符串
     * @param $len  截取长度[字节]
     * @param int $start    截取开始位置，默认0
     * @return string
     */
    private function mgCnSubstr($str,$len,$start = 0){
        $qStr = '';
        $qStrlen = ($start + $len)>strlen($str) ? strlen($str) : ($start + $len);

        //如果start不为起始位置，若起始位置为乱码就按照UTF-8编码获取新start
        if($start and json_encode(substr($str,$start,1)) === false){
            for($a=0;$a<3;$a++){
                $new_start = $start + $a;
                $m_str = substr($str,$new_start,3);
                if(json_encode($m_str) !== false) {
                    $start = $new_start;
                    break;
                }
            }
        }

        //切取内容
        for($i=$start; $i<$qStrlen; $i++){
            //ord()函数取得substr()的第一个字符的ASCII码，如果大于0xa0的话则是中文字符
            if(ord(substr($str,$i,1))>0xa0){
                $qStr .= substr($str,$i,3);
                $i+=2;
            }else{
                $qStr .= substr($str,$i,1);
            }
        }
        return $qStr;
    }
}