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

use Goods\Service\CouponRuleService;
use Zend\Form\Element\Csrf;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class CartController extends AbstractActionController
{
    private $dbTables = array();
    private $translator;

    /**
     * 购物车
     * @return array|multitype
     */
    public function indexAction()
    {
        $array = array();
        $this->layout()->dbTongJiPage = 'cart';
        $this->layout()->title_name = $this->getDbshopLang()->translate('购物车');

        $array['cart_array'] = $this->getServiceLocator()->get('frontHelper')->getCartSession();

        //用户优惠与积分中的计算
        $array = $this->getServiceLocator()->get('frontHelper')->promotionsOrIntegralFun($array, array('group_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('group_id')));

        return $array;
    }
    public function setaddressAction()
    {
        //判断是否已经登录或被删除
        $this->checkUserLoginOrDelete();

        $array = array();
        $this->layout()->title_name = $this->getDbshopLang()->translate('选择收货地址');
        //购物车商品
        $array['cart_array'] = $this->getServiceLocator()->get('frontHelper')->getCartSession();
        if(count($array['cart_array']) <= 0) return $this->redirect()->toRoute('mobile/default');

        //检查是购物车中是否全部为虚拟商品，如果是，则跳过配送地址选择
        if($this->checkCartVirtualGoods($array['cart_array'])) return $this->redirect()->toRoute('m_cart/default', array('action'=>'stepvirtual'));

        //收货地址
        $array['address_list'] = $this->getDbshopTable('UserAddressTable')->listAddress(array('user_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')));

        return $array;
    }
    public function addAddressAction()
    {
        //判断是否已经登录或被删除
        $this->checkUserLoginOrDelete();

        $array = array();
        $this->layout()->title_name = $this->getDbshopLang()->translate('添加收货地址');

        $addressQueryId = (int) $this->request->getQuery('address_id');
        $addressInfo = array();
        if($addressQueryId > 0) {
            $addressInfo = $this->getDbshopTable('UserAddressTable')->infoAddress(array('address_id'=>$addressQueryId, 'user_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')));
            if(empty($addressInfo)) return $this->redirect()->toRoute('m_cart/default', array('action'=>'setaddress'));
        }

        if($this->request->isPost()) {
            $addressId    = (int) $this->request->getPost('address_id');
            $addressArray = $this->request->getPost()->toArray();
            $addressArray['user_id'] = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');

            //添加收货地址时，进行csrf验证
            $csrfValid = new \Zend\Validator\Csrf(array('name'=>'address_security'));
            if(!$csrfValid->isValid($addressArray['address_security'])) {
                echo "<script>alert('".$this->getDbshopLang()->translate('非正常路径操作或者超时，请重新操作！')."');</script>";
            }
            if($addressId == 0) {
                if($this->getDbshopTable('UserAddressTable')->addAddress($addressArray)) return $this->redirect()->toRoute('m_cart/default', array('action'=>'setaddress'));
            } else {
                if($this->getDbshopTable('UserAddressTable')->updateAddress($addressArray, array('address_id'=>$addressId, 'user_id'=>$addressArray['user_id']))) return $this->redirect()->toRoute('m_cart/default', array('action'=>'setaddress'));
            }
        }

        $array['address_info']= $addressInfo;

        $array['region_array']= $this->getDbshopTable('RegionTable')->listRegion(array('dbshop_region.region_top_id=0'));
        //收货地址添加的csrf
        $csrf = new Csrf('address_security');
        $array['address_csrf'] = $csrf->getAttributes();

        return $array;
    }
    /**
     * 购物车第三步，选择配送方式、支付方式、确认订单
     * @return number|multitype:multitype:Ambigous <multitype:, \Zend\Config\Reader\mixed, string>
     */
    public function stepAction ()
    {
        //判断是否已经登录或被删除
        $this->checkUserLoginOrDelete();
        $array = array();
        $this->layout()->title_name = $this->getDbshopLang()->translate('订单确认');

        //购物车商品
        $array['cart_array'] = $this->getServiceLocator()->get('frontHelper')->getCartSession();
        $step = trim($this->request->getPost('step'));
        if(count($array['cart_array']) <= 0 or $step != 'setaddress') return $this->redirect()->toRoute('mobile/default');

        //购物车商品信息矫正，主要矫正的是商品价格，防止在商品被放入购物车后，后台管理员人员进行改价处理
        $this->checkCartGoodsPrice();
        //购物车商品
        $array['cart_array'] = $this->getServiceLocator()->get('frontHelper')->getCartSession();

        $cartTotalPrice = $this->getServiceLocator()->get('frontHelper')->getCartTotal();

        $userId = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');
        //检查是否有可用的优惠券
        $userCouponList = CouponRuleService::couponUseRule(array('cartGoods'=>$array['cart_array'], 'user_id'=>$userId), $this);
        $array['use_coupon'] = $userCouponList;

        /*----------------------配送方式与收货地址----------------------*/
        //商品总重量
        $array['total_weight'] = $this->getServiceLocator()->get('frontHelper')->getCartTotalWeight();
        //获取配送地址信息
        $addressId    = intval($this->request->getPost('user_address_id'));

        $addressInfo  = $this->getDbshopTable('UserAddressTable')->infoAddress(array('address_id'=>$addressId, 'user_id'=>$userId));
        if(!$addressInfo) return $this->redirect()->toRoute('m_cart/default');
        $array['address_info'] = $addressInfo;
        //获取配送地址对应的地区
        $regionInfo   = $this->getDbshopTable('RegionTable')->infoRegion(array('dbshop_region.region_id'=>$addressInfo['region_id']));
        $regionIdArray= explode(',', $regionInfo['region_path']);

        //获取配送方式信息
        $configReader = new \Zend\Config\Reader\Ini();
        $expressArray = array();
        $cashOnDelivery= '';
        $expressArray = $this->getDbshopTable('ExpressTable')->listExpress(array('express_state'=>1));
        if(is_array($expressArray) and !empty($expressArray)) {
            $expressConfigFilePath = DBSHOP_PATH . '/data/moduledata/Express/';
            $i = 0;
            $totalWeight = $array['total_weight'] * 1000;//将千克换算成克
            foreach ($expressArray as $value) {//循环读取配送方式的设置文件
                $expressExist = false;
                if(file_exists($expressConfigFilePath . $value['express_id'] . '.ini')) {
                    $expressConfig = $configReader->fromFile($expressConfigFilePath . $value['express_id'] . '.ini');
                    if($expressConfig['express_set'] == 'T') {//当为统一设置时进行的处理
                        $value['express_price'] = $this->getServiceLocator()->get('shop_express')->calculateCost(trim($expressConfig['express_price']), $totalWeight, $cartTotalPrice);
                        $expressExist = true;
                    } else {//当为个性化设置的时候，进行地区匹配
                        $value['express_price'] = 0;
                        foreach ($expressConfig['express_price'] as $price_value) {
                            $price_value['price'] = $this->getServiceLocator()->get('shop_express')->calculateCost(trim($price_value['price']), $totalWeight, $cartTotalPrice);
                            foreach ($regionIdArray as $regionId) {//循环查询，当前配送地址的地区id及其上级地区，是否在配送地区中有所体现，这里的foreach有点多了，待优化
                                if(in_array($regionId, $price_value['area_id'])) {
                                    $value['express_price'] = ($value['express_price'] > 0 and $value['express_price'] < $price_value['price']) ? $value['express_price'] : $price_value['price'];
                                    $expressExist = true;
                                }
                            }
                        }
                    }
                    //这是获取可以货到付款的配送方式的id字符串相连
                    if($value['cash_on_delivery'] == 1) $cashOnDelivery .= "'".$value['express_id'] . "',";
                }
                if($expressExist) {
                    $array['express_array'][$i] = $value;
                    $i++;
                }
            }
        }
        if(!empty($array['express_array'])) $array['express_array'][0]['selected'] = 1;
        $array['cash_on_delivery_str'] = (!empty($cashOnDelivery) ? substr($cashOnDelivery, 0, -1) : '');
        /*----------------------配送方式与收货地址----------------------*/

        /*----------------------支付方式----------------------*/
        $paymentArray = array();
        $filePath      = DBSHOP_PATH . '/data/moduledata/Payment/';
        if(is_dir($filePath)) {
            $dh = opendir($filePath);
            while (false !== ($fileName = readdir($dh))) {
                if($fileName != '.' and $fileName != '..' and stripos($fileName, '.php') !== false and $fileName != '.DS_Store' and $fileName != 'wxpay.php') {
                    $paymentInfo = include($filePath . $fileName);

                    //判断是否显示在当前平台
                    if(isset($paymentInfo['payment_show']['checked']) and !empty($paymentInfo['payment_show']['checked'])) {
                        $showArray = is_array($paymentInfo['payment_show']['checked']) ? $paymentInfo['payment_show']['checked'] : array($paymentInfo['payment_show']['checked']);
                        if(!in_array('phone', $showArray) and !in_array('all', $showArray)) continue;
                        //在微信内不显示支付宝支付，因为微信不支持；在手机浏览器上不能显示微信支付，因为手机浏览器不支持
                        if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false){
                            if(in_array($paymentInfo['editaction'], array('alipay', 'malipay', 'wxh5pay'))) continue;
                        } else {
                            if($paymentInfo['editaction'] == 'wxmpay') continue;
                        }

                    } else continue;

                    //判断是否符合当前的货币要求
                    $currencyState = false;
                    if(isset($paymentInfo['payment_currency']['checked']) and !empty($paymentInfo['payment_currency']['checked'])) {
                        $currencyArray = is_array($paymentInfo['payment_currency']['checked']) ? $paymentInfo['payment_currency']['checked'] : array($paymentInfo['payment_currency']['checked']);
                        $currencyState = in_array($this->getServiceLocator()->get('frontHelper')->getFrontDefaultCurrency(), $currencyArray) ? true : false;
                    } elseif (in_array($paymentInfo['editaction'], array('xxzf', 'hdfk'))) {//线下支付或者货到付款时，不进行货币判断
                        $currencyState = true;
                    }

                    if($paymentInfo['payment_state']['checked'] == 1 and $currencyState) {
                        $paymentInfo['payment_fee']['content'] = ((strpos($paymentInfo['payment_fee']['content'], '%') !== false) ? round($cartTotalPrice * str_replace('%', '', $paymentInfo['payment_fee']['content'])/100, 2) : round($this->getServiceLocator()->get('frontHelper')->shopPrice($paymentInfo['payment_fee']['content']), 2));
                        $paymentArray[] = $paymentInfo;
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
        $array['payment'] = $paymentArray;
        if(!empty($array['payment'])) $array['payment'][0]['selected'] = 1;
        /*----------------------支付方式----------------------*/

        //用户优惠与积分中的计算
        $array = $this->getServiceLocator()->get('frontHelper')->promotionsOrIntegralFun($array, array('group_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('group_id')));
        $array['promotionsCost'] = $this->getServiceLocator()->get('frontHelper')->shopPrice($array['promotionsCost']['discountCost']);

        //支付费用已经进行了汇率转换无需再次转换，配送费用未转换需要转换
        $expressPrice = isset($array['express_array'][0]['express_price']) ? $array['express_array'][0]['express_price'] : 0;
        $array['order_total'] = $this->getServiceLocator()->get('frontHelper')->shopPrice($expressPrice) + $array['payment'][0]['payment_fee']['content'] + $cartTotalPrice - $array['promotionsCost'];

        //会员信息，会员消费积分
        $array['user_info'] = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$userId));
        //获取积分转换比例
        $integralInfo = $this->getDbshopTable('UserIntegralTypeTable')->userIntegarlTypeOneInfo(array('integral_type_id'=>1));
        $currencyRate = $this->getServiceLocator()->get('frontHelper')->shopPriceRate();
        $array['integral_currency_con'] = $integralInfo->integral_currency_con / 100 * $currencyRate;

        //显示提交订单前信息的一些抛出
        $response = $this->getEventManager()->trigger('cart.step.front.post', $this, array('values'=>$array));
        if(!$response->isEmpty()) {
            $num = $response->count();
            for($i = 0; $i < $num; $i++) {
                $preArray = $response->offsetGet($i);
                if(!empty($preArray)) $array[key($preArray)] = current($preArray);
                unset($preArray);
            }
        }

        return $array;
    }
    /**
     * 购物车第三步(虚拟商品)，选择配送方式、支付方式、确认订单
     * @return array|multitype|\Zend\Http\Response
     */
    public function stepvirtualAction()
    {
        //判断是否已经登录或被删除
        $this->checkUserLoginOrDelete();
        $array = array();
        $this->layout()->title_name = $this->getDbshopLang()->translate('订单确认');

        //购物车商品
        $array['cart_array'] = $this->getServiceLocator()->get('frontHelper')->getCartSession();
        $step = trim($this->request->getPost('step'));
        if(count($array['cart_array']) <= 0 or !$this->checkCartVirtualGoods($array['cart_array'])) return $this->redirect()->toRoute('mobile/default');

        //购物车商品信息矫正，主要矫正的是商品价格，防止在商品被放入购物车后，后台管理员人员进行改价处理
        $this->checkCartGoodsPrice();
        //购物车商品
        $array['cart_array'] = $this->getServiceLocator()->get('frontHelper')->getCartSession();

        $cartTotalPrice = $this->getServiceLocator()->get('frontHelper')->getCartTotal();

        //检查是否有可用的优惠券
        $userCouponList = CouponRuleService::couponUseRule(array('cartGoods'=>$array['cart_array'], 'user_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')), $this);
        $array['use_coupon'] = $userCouponList;

        /*----------------------支付方式----------------------*/
        $paymentArray = array();
        $filePath      = DBSHOP_PATH . '/data/moduledata/Payment/';
        if(is_dir($filePath)) {
            $dh = opendir($filePath);
            while (false !== ($fileName = readdir($dh))) {
                if($fileName != '.' and $fileName != '..' and stripos($fileName, '.php') !== false and $fileName != '.DS_Store' and $fileName != 'wxpay.php') {
                    $paymentInfo = include($filePath . $fileName);

                    //判断是否显示在当前平台
                    if(isset($paymentInfo['payment_show']['checked']) and !empty($paymentInfo['payment_show']['checked'])) {
                        $showArray = is_array($paymentInfo['payment_show']['checked']) ? $paymentInfo['payment_show']['checked'] : array($paymentInfo['payment_show']['checked']);
                        if(!in_array('phone', $showArray) and !in_array('all', $showArray)) continue;
                        //在微信内不显示支付宝支付，因为微信不支持；在手机浏览器上不能显示微信支付，因为手机浏览器不支持
                        if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false){
                            if(in_array($paymentInfo['editaction'], array('alipay', 'malipay', 'wxh5pay'))) continue;
                        } else {
                            if($paymentInfo['editaction'] == 'wxmpay') continue;
                        }
                    } else continue;

                    //判断是否符合当前的货币要求
                    $currencyState = false;
                    if(isset($paymentInfo['payment_currency']['checked']) and !empty($paymentInfo['payment_currency']['checked'])) {
                        $currencyArray = is_array($paymentInfo['payment_currency']['checked']) ? $paymentInfo['payment_currency']['checked'] : array($paymentInfo['payment_currency']['checked']);
                        $currencyState = in_array($this->getServiceLocator()->get('frontHelper')->getFrontDefaultCurrency(), $currencyArray) ? true : false;
                    } elseif (in_array($paymentInfo['editaction'], array('xxzf', 'hdfk'))) {//线下支付或者货到付款时，不进行货币判断
                        $currencyState = true;
                    }

                    if($paymentInfo['payment_state']['checked'] == 1 and $currencyState) {
                        $paymentInfo['payment_fee']['content'] = ((strpos($paymentInfo['payment_fee']['content'], '%') !== false) ? round($cartTotalPrice * str_replace('%', '', $paymentInfo['payment_fee']['content'])/100, 2) : round($this->getServiceLocator()->get('frontHelper')->shopPrice($paymentInfo['payment_fee']['content']), 2));
                        $paymentArray[] = $paymentInfo;
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
        $array['payment'] = $paymentArray;
        if(!empty($array['payment'])) $array['payment'][0]['selected'] = 1;
        /*----------------------支付方式----------------------*/

        //用户优惠与积分中的计算
        $array = $this->getServiceLocator()->get('frontHelper')->promotionsOrIntegralFun($array, array('group_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('group_id')));
        $array['promotionsCost'] = $this->getServiceLocator()->get('frontHelper')->shopPrice($array['promotionsCost']['discountCost']);

        //支付费用已经进行了汇率转换无需再次转换，配送费用未转换需要转换
        $array['order_total'] = $array['payment'][0]['payment_fee']['content'] + $cartTotalPrice - $array['promotionsCost'];

        //会员信息，会员消费积分
        $array['user_info'] = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')));
        //获取积分转换比例
        $integralInfo = $this->getDbshopTable('UserIntegralTypeTable')->userIntegarlTypeOneInfo(array('integral_type_id'=>1));
        $currencyRate = $this->getServiceLocator()->get('frontHelper')->shopPriceRate();
        $array['integral_currency_con'] = $integralInfo->integral_currency_con / 100 * $currencyRate;

        //显示提交订单前信息的一些抛出
        $response = $this->getEventManager()->trigger('cart.stepvirtual.front.post', $this, array('values'=>$array));
        if(!$response->isEmpty()) {
            $num = $response->count();
            for($i = 0; $i < $num; $i++) {
                $preArray = $response->offsetGet($i);
                if(!empty($preArray)) $array[key($preArray)] = current($preArray);
                unset($preArray);
            }
        }

        return $array;
    }
    /**
     * 虚拟商品订单保存
     * @return multitype
     */
    public function submitvirtualAction()
    {
        $view = new ViewModel();
        $view->setTemplate('/mobile/cart/submit.phtml');

        //判断是否已经登录或被删除
        $this->checkUserLoginOrDelete();

        $this->layout()->dbTongJiPage = 'cartSubmit';
        $this->layout()->title_name  = $this->getDbshopLang()->translate('订单完成');

        $postArray = $this->request->getPost()->toArray();

        //事件驱动
        $this->getEventManager()->trigger('cart.submit.front.pre', $this, $postArray);

        //购物车商品
        $array['cart_array'] = $this->getServiceLocator()->get('frontHelper')->getCartSession();
        $step = trim($this->request->getPost('step'));
        if(count($array['cart_array']) <= 0 or $step != 'stepvirtual' or !$this->checkCartVirtualGoods($array['cart_array'])) return $this->redirect()->toRoute('mobile/default');

        $cartTotalPrice = $this->getServiceLocator()->get('frontHelper')->getCartTotal();

        $paymentArray = array();
        //因为这里之前的获取名称错写为 pyament_code 在此予以改正，但是担心用户没有及时更新模板，获取不到该值，所以做了下面这句判断
        $postArray['payment_code'] = isset($postArray['payment_code']) ? basename($postArray['payment_code']) : basename($postArray['pyament_code']);
        //获取支付方式信息
        if(file_exists(DBSHOP_PATH . '/data/moduledata/Payment/' . $postArray['payment_code'] . '.php')) {
            $paymentArray = include(DBSHOP_PATH . '/data/moduledata/Payment/' . $postArray['payment_code'] . '.php');
            $postArray['pay_name']    = $paymentArray['payment_name']['content'];
            $postArray['order_state'] = $paymentArray['orders_state'];

            //获取支付方式的手续费用，虽然在上一页面有传值过来，但是因为html有可能被恶意更改，因此这里从新获取计算
            $paymentFee = (!empty($paymentArray['payment_fee']['content']) ? ((strpos($paymentArray['payment_fee']['content'], '%') !== false) ? round($cartTotalPrice * str_replace('%', '', $paymentArray['payment_fee']['content'])/100, 2) : round($this->getServiceLocator()->get('frontHelper')->shopPrice($paymentArray['payment_fee']['content']), 2)) : 0);
        }

        //用户优惠与积分中的计算
        $array = $this->getServiceLocator()->get('frontHelper')->promotionsOrIntegralFun($array, array('group_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('group_id')));
        $array['promotionsCost'] = $this->getServiceLocator()->get('frontHelper')->shopPrice($array['promotionsCost']['discountCost']);

        //查看是否有优惠券使用
        $userCouponFee = 0;
        $userCouponState = false;
        $userCouponId  = 0;
        if(isset($postArray['coupon_id']) and $postArray['coupon_id'] > 0) {
            $userCoupon = $this->getDbshopTable('UserCouponTable')->infoUserCoupon(array('user_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id'), 'coupon_use_state'=>1, 'coupon_id'=>$postArray['coupon_id']));
            if($userCoupon->user_coupon_id > 0) {
                $couponFee = CouponRuleService::couponUseRule(array('cartGoods'=>$array['cart_array'], 'type'=>'submit_cart', 'coupon_id'=>$userCoupon->coupon_id), '');
                if($couponFee > 0) {
                    $userCouponFee  = $this->getServiceLocator()->get('frontHelper')->shopPrice($couponFee);
                    $userCouponState= true;
                    $userCouponId   = $userCoupon->user_coupon_id;
                }
            }
        }

        //订单总金额
        $orderTotalPrice = $paymentFee + $cartTotalPrice - $array['promotionsCost'] - $userCouponFee;

        //是否使用消费积分购买
        $integralBuyState = false;
        $integralBuyPrice = 0;
        $integralBuyNum   = (isset($postArray['integral_buy_num']) and $postArray['integral_buy_num'] > 0) ? intval($postArray['integral_buy_num']) : 0;
        $cartIntegralNum  = $this->getServiceLocator()->get('frontHelper')->getCartTotalIntegral();
        $userIntegralInfo = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')));
        if($userIntegralInfo->user_integral_num < $integralBuyNum or $integralBuyNum > $cartIntegralNum) $integralBuyNum = 0;//如果会员拥有的积分数小于将要购买使用的积分数或者购买积分数大于购物车中的积分数，则将该购买积分数设置为0，因为无效
        if($integralBuyNum > 0 and $cartIntegralNum > 0) {//只有已经使用的积分数和购物车中的积分数都大于0时，积分购买才开启
            //计算积分被转换为货币后的价值是否超过订单总金额，如果超过，则进行无效处理
            $integralInfo = $this->getDbshopTable('UserIntegralTypeTable')->userIntegarlTypeOneInfo(array('integral_type_id'=>1));
            $integralBuyPrice   = $this->getServiceLocator()->get('frontHelper')->shopPrice($integralInfo->integral_currency_con / 100 * $integralBuyNum);
            if($integralBuyPrice <= 0 or $integralBuyPrice > $orderTotalPrice) {
                $integralBuyNum     = 0;
                $integralBuyPrice   = 0;
            } else {
                $integralBuyState   = true;
                $orderTotalPrice    = $orderTotalPrice - $integralBuyPrice;
            }
        }
        //判断订单金额
        if($orderTotalPrice < 0) {
            $returnUrl = '<a href="'.$this->url()->fromRoute('m_cart/default').'"><strong>'.$this->getDbshopLang()->translate('点击返回购物车').'</strong></a>';
            exit($this->getDbshopLang()->translate('订单金额不能小于0!').$returnUrl);
        }
        /*----------------------订单相关信息保存----------------------*/
        //开启数据库事务处理
        $this->getDbshopTable('dbshopTransaction')->DbshopTransactionBegin();

        //对post过来的价格信息进行重置，以防止恶意使用者修改html中的数值
        $postArray['goods_total_price'] = $cartTotalPrice;
        $postArray['order_total_price'] = $orderTotalPrice;
        $postArray['pay_price']         = $paymentFee;
        $postArray['express_price']     = 0;
        $postArray['buy_pre_price']     = $array['promotionsCost'];
        $postArray['user_pre_price']    = $postArray['user_pre_price'];
        $postArray['integral_buy_num']  = $integralBuyNum;
        $postArray['integral_buy_price']= $integralBuyPrice;
        $postArray['integral_num']      = $array['integralInfo']['integralNum'];
        $postArray['integral_rule_info']= $array['integralInfo']['integalRuleInfo'];
        $postArray['integral_type_2_num']          = $array['integralInfo1']['integralNum'];
        $postArray['integral_type_2_num_rule_info']= $array['integralInfo1']['integalRuleInfo'];

        $orderArray = $this->orderSave($postArray);
        $orderId    = $orderArray['order_id'];

        //消费积分，消费记录保存
        if($integralBuyState) {
            $integralLogArray = array();
            $integralLogArray['user_id']           = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');
            $integralLogArray['user_name']         = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_name');
            $integralLogArray['integral_log_info'] = $this->getDbshopLang()->translate('商品购物，订单号为：') . $orderArray['order_sn'];
            $integralLogArray['integral_num_log']  = '-'.$integralBuyNum;
            $integralLogArray['integral_log_time'] = time();
            if($this->getDbshopTable('IntegralLogTable')->addIntegralLog($integralLogArray)) {
                //会员消费积分更新
                $this->getDbshopTable('UserTable')->updateUserIntegralNum($integralLogArray, array('user_id'=>$integralLogArray['user_id']));
            }
        }

        //优惠券使用处理，说明上面已经使用，这里将其状态修改为已经使用
        if($userCouponState and $userCouponId > 0) {
            $this->getDbshopTable('UserCouponTable')->updateUserCoupon(
                array(
                    'coupon_use_state'  => 2,
                    'used_order_id'     => $orderId,
                    'used_order_sn'     => $orderArray['order_sn'],
                    'used_time'         => time()
                ),
                array('user_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id'), 'user_coupon_id'=>$userCouponId, 'coupon_use_state'=>1));
            //在订单表中加入优惠券优惠金额
            $this->getDbshopTable('OrderTable')->updateOrder(array('coupon_pre_fee'=>$userCouponFee), array('order_id'=>$orderId));
        }

        //保存订单中的商品信息
        $goodsSerialize  = array();
        $goodsStockError = array();
        foreach ($array['cart_array'] as $cart_key => $cart_value) {
            $orderGoodsId = $this->orderGoodsSave($cart_value, array('order_id'=>$orderId));
            if($orderGoodsId != -1) {//库存正确处理
                $goodsSerialize[$orderGoodsId] = array(
                    'goods_id'          => $cart_value['goods_id'],
                    'class_id'          => $cart_value['class_id'],
                    'goods_name'        => $cart_value['goods_name'],
                    'goods_extend_info' => $cart_value['goods_color_name'] . $cart_value['goods_size_name'] . str_replace('<br>', '  ',$cart_value['goods_adv_tag_name']),
                    'goods_image'       => $cart_value['goods_image'],
                    'goods_type'        => $cart_value['goods_type'],
                    'goods_shop_price'  => $this->getServiceLocator()->get('frontHelper')->shopPrice($cart_value['goods_shop_price']),
                    'buy_num'           => $cart_value['buy_num'],
                    'goods_color'       => isset($cart_value['goods_color']) ? $cart_value['goods_color'] : '',
                    'goods_size'        => isset($cart_value['goods_size']) ? $cart_value['goods_size'] : '',
                    'goods_spec_tag_id' => $cart_value['goods_adv_tag_id']
                );
            } else {
                $goodsStockError[] = $cart_value['goods_name'];
            }
        }
        //判断库存是否不足，如果不足，则启用事务回滚功能
        if(!empty($goodsStockError)) {
            $this->getDbshopTable('dbshopTransaction')->DbshopTransactionRollback();//事务回滚
            $errorMessage = implode('<br>', $goodsStockError) . '<br>' . $this->getDbshopLang()->translate('商品库存不足') . '<a href="'.$this->url()->fromRoute('m_cart/default').'">' . $this->getDbshopLang()->translate('去购物车中删除库存不足的商品') . '</a>';
            exit($errorMessage);
        } else {
            $this->getDbshopTable('dbshopTransaction')->DbshopTransactionCommit();//事务确认
        }

        $this->getDbshopTable('OrderTable')->updateOrder(array('goods_serialize'=>serialize($goodsSerialize)), array('order_id'=>$orderId));
        //事件驱动的数据
        $postArray['order_id']  = $orderId;
        $postArray['other']     =  array(
            'user_id'   => $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id'),
            'user_name' => $this->getServiceLocator()->get('frontHelper')->getUserSession('user_name'),
            'user_group'=> $this->getServiceLocator()->get('frontHelper')->getUserSession('group_id'),
            'cartGoods' => $array['cart_array'],
            'order_id'  => $orderId,
            'order_sn'  => $orderArray['order_sn']
        );
        //清空购物车操作
        $this->getServiceLocator()->get('frontHelper')->clearCartSession();
        /*----------------------订单相关信息保存----------------------*/

        $array['order_sn']    = $orderArray['order_sn'];
        $array['order_id']    = $orderId;
        $array['order_state'] = $postArray['order_state'];
        $array['order_total'] = $this->getServiceLocator()->get('frontHelper')->shopPriceSymbol() . $postArray['order_total_price'] . $this->getServiceLocator()->get('frontHelper')->shopPriceUnit();

        //事件驱动
        $this->getEventManager()->trigger('cart.submit.front.post', $this, $postArray);

        /*----------------------提醒信息发送----------------------*/
        $sendMessageBody = $this->getServiceLocator()->get('frontHelper')->getSendMessageBody('submit_order');
        if($sendMessageBody != '') {
            $sendArray = array();
            $sendArray['shopname']      = $this->getServiceLocator()->get('frontHelper')->websiteInfo('shop_name');
            $sendArray['buyname']       = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_name');
            $sendArray['ordersn']       = $array['order_sn'];
            $sendArray['ordertotal']    = $array['order_total'];
            $sendArray['submittime']    = $orderArray['order_time'];
            $sendArray['shopurl']       = $this->getServiceLocator()->get('frontHelper')->dbshopHttpOrHttps() . $this->getServiceLocator()->get('frontHelper')->dbshopHttpHost() . $this->url()->fromRoute('shopfront/default');

            $sendArray['subject']       = $sendArray['shopname'] . $this->getDbshopLang()->translate('提交订单提醒');
            $sendArray['send_mail'][]   = $this->getServiceLocator()->get('frontHelper')->getSendMessageBuyerEmail('submit_order_state', $this->getServiceLocator()->get('frontHelper')->getUserSession('user_email'));
            $sendArray['send_mail'][]   = $this->getServiceLocator()->get('frontHelper')->getSendMessageAdminEmail('submit_order_state');

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
        /*----------------------提醒信息发送----------------------*/
        /*----------------------手机短信信息发送----------------------*/
        $smsData = array(
            'shopname'   => $sendArray['shopname'],
            'buyname'    => $sendArray['buyname'],
            'ordersn'    => $sendArray['ordersn'],
            'submittime' => $sendArray['submittime'],
            'ordertotal' => $sendArray['ordertotal']
        );
        try {
            $this->getServiceLocator()->get('shop_send_sms')->toSendSms(
                $smsData,
                $this->getServiceLocator()->get('frontHelper')->getUserSession('user_phone'),
                'alidayu_submit_order_template_id',
                $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')
            );
        } catch(\Exception $e) {

        }
        /*----------------------手机短信信息发送----------------------*/

        $view->setVariables($array);
        return $view;
    }
    /**
     * 订单提交
     */
    public function submitAction()
    {
        //判断是否已经登录或被删除
        $this->checkUserLoginOrDelete();

        $this->layout()->dbTongJiPage = 'cartSubmit';
        $this->layout()->title_name  = $this->getDbshopLang()->translate('订单完成');

        $postArray = $this->request->getPost()->toArray();

        //事件驱动
        $this->getEventManager()->trigger('cart.submit.front.pre', $this, $postArray);

        //购物车商品
        $array['cart_array'] = $this->getServiceLocator()->get('frontHelper')->getCartSession();
        $step = trim($this->request->getPost('step'));
        if(count($array['cart_array']) <= 0 or $step != 'step' or intval($postArray['express_id']) == 0) return $this->redirect()->toRoute('mobile/default');

        $cartTotalPrice = $this->getServiceLocator()->get('frontHelper')->getCartTotal();

        $paymentArray = array();
        //因为这里之前的获取名称错写为 pyament_code 在此予以改正，但是担心用户没有及时更新模板，获取不到该值，所以做了下面这句判断
        $postArray['payment_code'] = isset($postArray['payment_code']) ? basename($postArray['payment_code']) : basename($postArray['pyament_code']);
        //获取支付方式信息
        if(file_exists(DBSHOP_PATH . '/data/moduledata/Payment/' . $postArray['payment_code'] . '.php')) {
            $paymentArray = include(DBSHOP_PATH . '/data/moduledata/Payment/' . $postArray['payment_code'] . '.php');
            $postArray['pay_name']    = $paymentArray['payment_name']['content'];
            $postArray['order_state'] = $paymentArray['orders_state'];

            //获取支付方式的手续费用，虽然在上一页面有传值过来，但是因为html有可能被恶意更改，因此这里从新获取计算
            $paymentFee = (!empty($paymentArray['payment_fee']['content']) ? ((strpos($paymentArray['payment_fee']['content'], '%') !== false) ? round($cartTotalPrice * str_replace('%', '', $paymentArray['payment_fee']['content'])/100, 2) : round($this->getServiceLocator()->get('frontHelper')->shopPrice($paymentArray['payment_fee']['content']), 2)) : 0);
        }

        $userId = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');
        //获取配送方式信息
        $expressArray = array();
        $expressArray = $this->getDbshopTable('ExpressTable')->infoExpress(array('express_id'=>$postArray['express_id'], 'express_state'=>1));
        $postArray['express_id']   = $expressArray->express_id;
        $postArray['express_name'] = $expressArray->express_name;
        //配送费用获取
        $addressInfo  = $this->getDbshopTable('UserAddressTable')->infoAddress(array('address_id'=>$postArray['address_id'], 'user_id'=>$userId));//对应下面的收货地址保存
        if(!$addressInfo) return $this->redirect()->toRoute('m_cart/default');

        $regionInfo   = $this->getDbshopTable('RegionTable')->infoRegion(array('dbshop_region.region_id'=>$addressInfo['region_id']));
        $regionIdArray= explode(',', $regionInfo['region_path']);

        $totalWeight = $this->getServiceLocator()->get('frontHelper')->getCartTotalWeight() * 1000;//将千克换算成克

        $configReader = new \Zend\Config\Reader\Ini();
        $expressConfigFilePath = DBSHOP_PATH . '/data/moduledata/Express/';
        $expressConfig = $configReader->fromFile($expressConfigFilePath . $postArray['express_id'] . '.ini');
        if($expressConfig['express_set'] == 'T') {//当为统一设置时进行的处理
            $expressPrice = $this->getServiceLocator()->get('shop_express')->calculateCost(trim($expressConfig['express_price']), $totalWeight, $cartTotalPrice);
        } else {
            $expressPrice = 0;
            foreach ($expressConfig['express_price'] as $priceValue) {
                $priceValue['price'] = $this->getServiceLocator()->get('shop_express')->calculateCost(trim($priceValue['price']), $totalWeight, $cartTotalPrice);
                foreach ($regionIdArray as $regionId) {
                    if(in_array($regionId, $priceValue['area_id'])) {
                        $expressPrice = ($expressPrice > 0 and $expressPrice < $priceValue['price']) ? $expressPrice : $priceValue['price'];
                    }
                }
            }
        }
        $expressPrice = $this->getServiceLocator()->get('frontHelper')->shopPrice($expressPrice);

        //用户优惠与积分中的计算
        $array = $this->getServiceLocator()->get('frontHelper')->promotionsOrIntegralFun($array, array('group_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('group_id')));
        $array['promotionsCost'] = $this->getServiceLocator()->get('frontHelper')->shopPrice($array['promotionsCost']['discountCost']);

        //查看是否有优惠券使用
        $userCouponFee = 0;
        $userCouponState = false;
        $userCouponId  = 0;
        if(isset($postArray['coupon_id']) and $postArray['coupon_id'] > 0) {
            $userCoupon = $this->getDbshopTable('UserCouponTable')->infoUserCoupon(array('user_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id'), 'coupon_use_state'=>1, 'coupon_id'=>$postArray['coupon_id']));
            if($userCoupon->user_coupon_id > 0) {
                $couponFee = CouponRuleService::couponUseRule(array('cartGoods'=>$array['cart_array'], 'type'=>'submit_cart', 'coupon_id'=>$userCoupon->coupon_id), '');
                if($couponFee > 0) {
                    $userCouponFee  = $this->getServiceLocator()->get('frontHelper')->shopPrice($couponFee);
                    $userCouponState= true;
                    $userCouponId   = $userCoupon->user_coupon_id;
                }
            }
        }

        //订单总金额
        $orderTotalPrice = $expressPrice + $paymentFee + $cartTotalPrice - $array['promotionsCost'] - $userCouponFee;

        //是否使用消费积分购买
        $integralBuyState = false;
        $integralBuyPrice = 0;
        $integralBuyNum   = (isset($postArray['integral_buy_num']) and $postArray['integral_buy_num'] > 0) ? intval($postArray['integral_buy_num']) : 0;
        $cartIntegralNum  = $this->getServiceLocator()->get('frontHelper')->getCartTotalIntegral();
        $userIntegralInfo = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')));
        if($userIntegralInfo->user_integral_num < $integralBuyNum or $integralBuyNum > $cartIntegralNum) $integralBuyNum = 0;//如果会员拥有的积分数小于将要购买使用的积分数或者购买积分数大于购物车中的积分数，则将该购买积分数设置为0，因为无效
        if($integralBuyNum > 0 and $cartIntegralNum > 0) {//只有已经使用的积分数和购物车中的积分数都大于0时，积分购买才开启
            //计算积分被转换为货币后的价值是否超过订单总金额，如果超过，则进行无效处理
            $integralInfo = $this->getDbshopTable('UserIntegralTypeTable')->userIntegarlTypeOneInfo(array('integral_type_id'=>1));
            $integralBuyPrice   = $this->getServiceLocator()->get('frontHelper')->shopPrice($integralInfo->integral_currency_con / 100 * $integralBuyNum);
            if($integralBuyPrice <= 0 or $integralBuyPrice > $orderTotalPrice) {
                $integralBuyNum     = 0;
                $integralBuyPrice   = 0;
            } else {
                $integralBuyState   = true;
                $orderTotalPrice    = $orderTotalPrice - $integralBuyPrice;
            }
        }
        //判断订单金额
        if($orderTotalPrice < 0) {
            $returnUrl = '<a href="'.$this->url()->fromRoute('m_cart/default').'"><strong>'.$this->getDbshopLang()->translate('点击返回购物车').'</strong></a>';
            exit($this->getDbshopLang()->translate('订单金额不能小于0!').$returnUrl);
        }

        /*----------------------订单相关信息保存----------------------*/
        //开启数据库事务处理
        $this->getDbshopTable('dbshopTransaction')->DbshopTransactionBegin();

        //对post过来的价格信息进行重置，以防止恶意使用者修改html中的数值
        $postArray['goods_total_price'] = $cartTotalPrice;
        $postArray['order_total_price'] = $orderTotalPrice;
        $postArray['pay_price']         = $paymentFee;
        $postArray['express_price']     = $expressPrice;
        $postArray['buy_pre_price']     = $array['promotionsCost'];
        $postArray['user_pre_price']    = $postArray['user_pre_price'];
        $postArray['integral_buy_num']  = $integralBuyNum;
        $postArray['integral_buy_price']= $integralBuyPrice;
        $postArray['integral_num']      = $array['integralInfo']['integralNum'];
        $postArray['integral_rule_info']= $array['integralInfo']['integalRuleInfo'];
        $postArray['integral_type_2_num']          = $array['integralInfo1']['integralNum'];
        $postArray['integral_type_2_num_rule_info']= $array['integralInfo1']['integalRuleInfo'];

        $orderArray = $this->orderSave($postArray);
        $orderId    = $orderArray['order_id'];

        //消费积分，消费记录保存
        if($integralBuyState) {
            $integralLogArray = array();
            $integralLogArray['user_id']           = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');
            $integralLogArray['user_name']         = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_name');
            $integralLogArray['integral_log_info'] = $this->getDbshopLang()->translate('商品购物，订单号为：') . $orderArray['order_sn'];
            $integralLogArray['integral_num_log']  = '-'.$integralBuyNum;
            $integralLogArray['integral_log_time'] = time();
            if($this->getDbshopTable('IntegralLogTable')->addIntegralLog($integralLogArray)) {
                //会员消费积分更新
                $this->getDbshopTable('UserTable')->updateUserIntegralNum($integralLogArray, array('user_id'=>$integralLogArray['user_id']));
            }
        }

        //保存收货地址
        $this->orderDeliveryAddressSave($addressInfo, array('order_id'=>$orderId,'shipping_time'=>$postArray['shipping_time'], 'express_name'=>$postArray['express_name'], 'express_id'=>$postArray['express_id'], 'express_fee'=>$postArray['express_price']));

        //优惠券使用处理，说明上面已经使用，这里将其状态修改为已经使用
        if($userCouponState and $userCouponId > 0) {
            $this->getDbshopTable('UserCouponTable')->updateUserCoupon(
                array(
                    'coupon_use_state'  => 2,
                    'used_order_id'     => $orderId,
                    'used_order_sn'     => $orderArray['order_sn'],
                    'used_time'         => time()
                ),
                array('user_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id'), 'user_coupon_id'=>$userCouponId, 'coupon_use_state'=>1));
            //在订单表中加入优惠券优惠金额
            $this->getDbshopTable('OrderTable')->updateOrder(array('coupon_pre_fee'=>$userCouponFee), array('order_id'=>$orderId));
        }

        //保存订单中的商品信息
        $goodsSerialize  = array();
        $goodsStockError = array();
        foreach ($array['cart_array'] as $cart_key => $cart_value) {
            $orderGoodsId = $this->orderGoodsSave($cart_value, array('order_id'=>$orderId));
            if($orderGoodsId != -1) {//库存正确处理
                $goodsSerialize[$orderGoodsId] = array(
                    'goods_id'          => $cart_value['goods_id'],
                    'class_id'          => $cart_value['class_id'],
                    'goods_name'        => $cart_value['goods_name'],
                    'goods_extend_info' => $cart_value['goods_color_name'] . $cart_value['goods_size_name'] . str_replace('<br>', '  ',$cart_value['goods_adv_tag_name']),
                    'goods_image'       => $cart_value['goods_image'],
                    'goods_type'        => $cart_value['goods_type'],
                    'goods_shop_price'  => $this->getServiceLocator()->get('frontHelper')->shopPrice($cart_value['goods_shop_price']),
                    'buy_num'           => $cart_value['buy_num'],
                    'goods_color'       => isset($cart_value['goods_color']) ? $cart_value['goods_color'] : '',
                    'goods_size'        => isset($cart_value['goods_size']) ? $cart_value['goods_size'] : '',
                    'goods_spec_tag_id' => $cart_value['goods_adv_tag_id']
                );
            } else {
                $goodsStockError[] = $cart_value['goods_name'];
            }
        }
        //判断库存是否不足，如果不足，则启用事务回滚功能
        if(!empty($goodsStockError)) {
            $this->getDbshopTable('dbshopTransaction')->DbshopTransactionRollback();//事务回滚
            $errorMessage = implode('<br>', $goodsStockError) . '<br>' . $this->getDbshopLang()->translate('商品库存不足') . '<a href="'.$this->url()->fromRoute('m_cart/default').'">' . $this->getDbshopLang()->translate('去购物车中删除库存不足的商品') . '</a>';
            exit($errorMessage);
        } else {
            $this->getDbshopTable('dbshopTransaction')->DbshopTransactionCommit();//事务确认
        }

        $this->getDbshopTable('OrderTable')->updateOrder(array('goods_serialize'=>serialize($goodsSerialize)), array('order_id'=>$orderId));
        //事件驱动的数据
        $postArray['order_id']  = $orderId;
        $postArray['other']     =  array(
            'user_id'   => $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id'),
            'user_name' => $this->getServiceLocator()->get('frontHelper')->getUserSession('user_name'),
            'user_group'=> $this->getServiceLocator()->get('frontHelper')->getUserSession('group_id'),
            'cartGoods' => $array['cart_array'],
            'order_id'  => $orderId,
            'order_sn'  => $orderArray['order_sn']
        );
        //清空购物车操作
        $this->getServiceLocator()->get('frontHelper')->clearCartSession();
        /*----------------------订单相关信息保存----------------------*/

        $array['order_sn']    = $orderArray['order_sn'];
        $array['order_id']    = $orderId;
        $array['order_state'] = $postArray['order_state'];
        $array['order_total'] = $this->getServiceLocator()->get('frontHelper')->shopPriceSymbol() . $postArray['order_total_price'] . $this->getServiceLocator()->get('frontHelper')->shopPriceUnit();

        //事件驱动
        $this->getEventManager()->trigger('cart.submit.front.post', $this, $postArray);

        /*----------------------提醒信息发送----------------------*/
        $sendMessageBody = $this->getServiceLocator()->get('frontHelper')->getSendMessageBody('submit_order');
        if($sendMessageBody != '') {
            $sendArray = array();
            $sendArray['shopname']      = $this->getServiceLocator()->get('frontHelper')->websiteInfo('shop_name');
            $sendArray['buyname']       = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_name');
            $sendArray['ordersn']       = $array['order_sn'];
            $sendArray['ordertotal']    = $array['order_total'];
            $sendArray['submittime']    = $orderArray['order_time'];
            $sendArray['shopurl']       = $this->getServiceLocator()->get('frontHelper')->dbshopHttpOrHttps() . $this->getServiceLocator()->get('frontHelper')->dbshopHttpHost() . $this->url()->fromRoute('shopfront/default');

            $sendArray['subject']       = $sendArray['shopname'] . $this->getDbshopLang()->translate('提交订单提醒');
            $sendArray['send_mail'][]   = $this->getServiceLocator()->get('frontHelper')->getSendMessageBuyerEmail('submit_order_state', $this->getServiceLocator()->get('frontHelper')->getUserSession('user_email'));
            $sendArray['send_mail'][]   = $this->getServiceLocator()->get('frontHelper')->getSendMessageAdminEmail('submit_order_state');

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
        /*----------------------提醒信息发送----------------------*/
        /*----------------------手机短信信息发送----------------------*/
        $smsData = array(
            'shopname'   => $sendArray['shopname'],
            'buyname'    => $sendArray['buyname'],
            'ordersn'    => $sendArray['ordersn'],
            'submittime' => $sendArray['submittime'],
            'ordertotal' => $sendArray['ordertotal']
        );
        try {
            $this->getServiceLocator()->get('shop_send_sms')->toSendSms(
                $smsData,
                $this->getServiceLocator()->get('frontHelper')->getUserSession('user_phone'),
                'alidayu_submit_order_template_id',
                $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')
            );
        } catch(\Exception $e) {

        }
        /*----------------------手机短信信息发送----------------------*/

        return $array;
    }
    /**
     * 订单商品保存
     * @param array $goodsArray
     * @param array $data
     */
    private function orderGoodsSave (array $goodsArray, array $data)
    {
        //判断库存是否正常，如不正常，停止该次操作返回 -1
        if(!$this->goodsStockOper($goodsArray)) {
            return -1;
        }
        //正常，继续往下处理
        $array = array();
        $array['order_id']          = $data['order_id'];
        $array['goods_id']          = $goodsArray['goods_id'];
        $array['class_id']          = $goodsArray['class_id'];
        $array['goods_item']        = $goodsArray['goods_item'];
        $array['goods_name']        = $goodsArray['goods_name'];
        $array['goods_extend_info'] = ($goodsArray['goods_color_name'] != '' ? '<p>' . $goodsArray['goods_color_name'] . '</p>' : '')
            . ($goodsArray['goods_size_name'] != '' ? '<p>' . $goodsArray['goods_size_name'] . '</p>' : '')
            . ($goodsArray['goods_adv_tag_name'] != '' ? $goodsArray['goods_adv_tag_name'] : '');
        $array['goods_color']       = $goodsArray['goods_color'];
        $array['goods_size']        = $goodsArray['goods_size'];
        $array['goods_spec_tag_id'] = $goodsArray['goods_adv_tag_id'];
        $array['goods_shop_price']  = $this->getServiceLocator()->get('frontHelper')->shopPrice($goodsArray['goods_shop_price']);
        $array['goods_type']        = $goodsArray['goods_type'];
        $array['buy_num']           = $goodsArray['buy_num'];
        $array['goods_image']       = $goodsArray['goods_image'];
        $array['goods_amount']      = $this->getServiceLocator()->get('frontHelper')->shopPrice($goodsArray['goods_shop_price'] * $goodsArray['buy_num']);
        $array['goods_count_weight']= $goodsArray['goods_weight'] * $goodsArray['buy_num'];
        $array['buyer_id']          = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');

        return $this->getDbshopTable('OrderGoodsTable')->addOrderGoods($array);
    }
    /**
     * 库存操作处理
     * @param array $data
     * @return void|boolean
     */
    private function goodsStockOper(array $data)
    {
        $where       = array();
        $whereExtend = array();
        $stockNum    = 0;
        $extendState = false;//是否有扩展商品信息，默认是无

        $where['goods_id'] = $data['goods_id'];
        $goodsInfo         = $this->getDbshopTable('GoodsTable')->oneGoodsInfo($where);
        //当后台管理开启库存状态显示时，直接返回即可，无需扣除库存
        if($goodsInfo->goods_stock_state_open == 1) return true;
        //否则得到当前商品库存
        $stockNum          = $goodsInfo->goods_stock;
        //判断是否有规格，如果有规格获取扩展表中的商品信息
        if($goodsInfo->goods_spec_type == 2) {
            if(isset($data['goods_adv_tag_id']) and !empty($data['goods_adv_tag_id'])) {
                $whereExtend['goods_id']        = $data['goods_id'];
                $whereExtend['adv_spec_tag_id'] = $data['goods_adv_tag_id'];
                $extendGoods                = $this->getDbshopTable('GoodsPriceExtendGoodsTable')->InfoPriceExtendGoods($whereExtend);
                if($extendGoods) {
                    $stockNum    = $extendGoods->goods_extend_stock;//默认库存
                    $extendState = true;//有扩展信息
                }
            }
        } else {
            if(isset($data['goods_color']) and !empty($data['goods_color']) and isset($data['goods_size']) and !empty($data['goods_size'])) {
                $whereExtend['goods_id']    = $data['goods_id'];
                $whereExtend['goods_color'] = $data['goods_color'];
                $whereExtend['goods_size']  = $data['goods_size'];
                $extendGoods                = $this->getDbshopTable('GoodsPriceExtendGoodsTable')->InfoPriceExtendGoods($whereExtend);
                if($extendGoods) {
                    $stockNum    = $extendGoods->goods_extend_stock;//默认库存
                    $extendState = true;//有扩展信息
                }
            }
        }
        //判断库存是否符合要求，并于购物数量进行比较
        if($stockNum > 0 and $data['buy_num'] > 0 and $stockNum >= $data['buy_num']) {
            $stockNum = $stockNum - $data['buy_num'];
            if($extendState) {
                $this->getDbshopTable('GoodsPriceExtendGoodsTable')->updatePriceExtendGoods(array('goods_extend_stock'=>$stockNum), $whereExtend);
            } else {
                $this->getDbshopTable('GoodsTable')->oneUpdateGoods(array('goods_stock'=>$stockNum), $where);
            }
            return true;
        }

        return false;
    }
    /**
     * 保存订单收货地址
     * @param unknown $address
     * @param array $data
     */
    private function orderDeliveryAddressSave ($address, array $data)
    {
        $array = array();
        $array['order_id']      = $data['order_id'];
        $array['delivery_name'] = $address['true_name'];
        $array['region_id']     = $address['region_id'];
        $array['region_info']   = $address['region_value'];
        $array['region_address']= $address['address'];
        $array['zip_code']      = $address['zip_code'];
        $array['tel_phone']     = ($address['tel_area_code']=='' ? '' : $address['tel_area_code'] . '-') . $address['tel_phone'] . ($address['tel_ext']=='' ? '' : '-' . $address['tel_ext']);
        $array['mod_phone']     = $address['mod_phone'];
        $array['express_name']  = $data['express_name'];
        $array['express_time_info'] = $data['shipping_time'];
        $array['express_fee']   = $data['express_fee'];
        $array['express_id']    = $data['express_id'];

        $this->getDbshopTable('OrderDeliveryAddressTable')->addDeliveryAddress($array);
    }
    /**
     * 订单保存
     * @param array $orderArray
     * @return unknown
     */
    private function orderSave(array $orderArray)
    {
        $array = array();
        $array['order_id']            = '';
        $array['order_sn']            = $this->getServiceLocator()->get('frontHelper')->createOrderSn($this->getServiceLocator()->get('frontHelper')->getUserSession('user_id'));
        $array['order_out_sn']        = '';//out' . $array['order_sn'];
        $array['goods_amount']        = (empty($orderArray['goods_total_price']) ? '0.00' : $orderArray['goods_total_price']);
        $array['order_amount']        = (empty($orderArray['order_total_price']) ? '0.00' : $orderArray['order_total_price']);
        $array['pay_fee']             = $orderArray['pay_price'];
        $array['express_fee']         = $orderArray['express_price'];
        $array['user_pre_fee']        = 0;//$orderArray['user_pre_price'];会员优惠费用，暂时没有用到，属于预留功能点
        $array['user_pre_info']       = '';//$orderArray[''];
        $array['buy_pre_fee']         = $orderArray['buy_pre_price'];
        $array['integral_buy_num']    = $orderArray['integral_buy_num'];
        $array['integral_buy_price'] = $orderArray['integral_buy_price'];
        $array['goods_weight_amount'] = $orderArray['goods_count_weight'];
        $array['order_state']         = $orderArray['order_state'];
        $array['pay_code']            = $orderArray['payment_code'];
        $array['pay_name']            = $orderArray['pay_name'];
        $array['express_id']          = isset($orderArray['express_id']) ? $orderArray['express_id'] : '';
        $array['buyer_id']            = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');
        $array['buyer_name']          = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_name');
        $array['buyer_email']         = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_email');
        $array['express_name']        = isset($orderArray['express_name']) ? $orderArray['express_name'] : '';
        $array['order_time']          = time();
        $array['currency']            = $this->getServiceLocator()->get('frontHelper')->shopCurrency();
        $array['currency_symbol']     = $this->getServiceLocator()->get('frontHelper')->shopPriceSymbol();
        $array['currency_unit']       = $this->getServiceLocator()->get('frontHelper')->shopPriceUnit();
        $array['order_message']       = $orderArray['order_message'];
        $array['integral_num']        = $orderArray['integral_num'];
        $array['integral_rule_info']  = $orderArray['integral_rule_info'];
        $array['integral_type_2_num']            = $orderArray['integral_type_2_num'];
        $array['integral_type_2_num_rule_info']  = $orderArray['integral_type_2_num_rule_info'];

        //发票内容
        if($this->getServiceLocator()->get('frontHelper')->websiteInfo('shop_invoice') == 'true') {
            if($orderArray['invoice_content'] != '' or $orderArray['invoice_title']) {
                $array['invoice_content'] = $orderArray['navigation_type'] . ' - ';
                if(!empty($orderArray['invoice_title'])) $array['invoice_content'] .= $this->getDbshopLang()->translate('发票抬头').'：' . $orderArray['invoice_title'] . ' - ';
                if(!empty($orderArray['invoice_content'])) $array['invoice_content'] .= $this->getDbshopLang()->translate('发票内容').'：' . $orderArray['invoice_content'];
            }
        }

        $orderId = $this->getDbshopTable('OrderTable')->addOrder($array);

        return array('order_id'=>$orderId, 'order_sn'=>$array['order_sn'], 'order_time'=>$array['order_time']);
    }
    /**
     * 检查购物车中的商品价格是否有所改变
     */
    private function checkCartGoodsPrice()
    {
        //获取会员组id
        $userGroupId = $this->getServiceLocator()->get('frontHelper')->getUserSession('group_id');

        $cartGoods = $this->getServiceLocator()->get('frontHelper')->getCartSession();
        foreach($cartGoods as $cartKey => $goodsValue) {
            $colorValue = $goodsValue['goods_color'];
            $sizeValue  = $goodsValue['goods_size'];
            $goodsId    = $goodsValue['goods_id'];
            $specTagId  = $goodsValue['goods_adv_tag_id'];

            $goodsInfo = $this->getDbshopTable('GoodsTable')->infoGoods(array('dbshop_goods.goods_id'=>$goodsValue['goods_id'], 'dbshop_goods.goods_state'=>1));
            if(!$goodsInfo) {
                $this->getServiceLocator()->get('frontHelper')->delCartSession($cartKey);
                continue;
            } else {
                //判断优惠价格是否存在，是否过期
                $preferentialStart = (intval($goodsInfo->goods_preferential_start_time) == 0 or time() >= $goodsInfo->goods_preferential_start_time) ? true : false;
                $preferentialEnd   = (intval($goodsInfo->goods_preferential_end_time) == 0 or time() <= $goodsInfo->goods_preferential_end_time) ? true : false;
                $goodsInfo->goods_preferential_price = ($preferentialStart and $preferentialEnd and $goodsInfo->goods_preferential_price > 0) ? $goodsInfo->goods_preferential_price : 0;
                //当颜色和尺寸同时存在情况下，进行检查获取商品库存及颜色和尺寸值，赋值入基础商品中
                if((isset($colorValue) and !empty($colorValue)) and (isset($sizeValue) and !empty($sizeValue)) or (isset($specTagId) and !empty($specTagId))) {
                    if($goodsInfo->goods_spec_type == 2) {
                        $extendGoods = $this->getDbshopTable('GoodsPriceExtendGoodsTable')->InfoPriceExtendGoods(array('adv_spec_tag_id'=>$specTagId, 'goods_id'=>$goodsId));
                        if(!$extendGoods) {
                            $this->getServiceLocator()->get('frontHelper')->delCartSession($cartKey);
                            continue;
                        }
                        $goodsInfo->goods_stock      = $extendGoods->goods_extend_stock;
                        $goodsInfo->goods_item       = $extendGoods->goods_extend_item;
                        $goodsInfo->goods_shop_price = ($goodsInfo->goods_preferential_price <= 0 ? $extendGoods->goods_extend_price : $goodsInfo->goods_preferential_price);
                        $goodsInfo->goods_adv_tag_id = $specTagId;
                        //当未开启优惠价，判断是否有会员价
                        if($goodsInfo->goods_preferential_price <= 0 and $userGroupId > 0) {
                            $userGroupPrice = $this->getDbshopTable('GoodsUsergroupPriceTable')->infoGoodsUsergroupPrice(array('goods_id'=>$goodsInfo->goods_id, 'user_group_id'=>$userGroupId, 'adv_spec_tag_id'=>$specTagId));
                            if(isset($userGroupPrice->goods_user_group_price) and $userGroupPrice->goods_user_group_price > 0) $goodsInfo->goods_shop_price = $userGroupPrice->goods_user_group_price;
                        }
                    } else {
                        $extendGoods = $this->getDbshopTable('GoodsPriceExtendGoodsTable')->InfoPriceExtendGoods(array('goods_color'=>$colorValue, 'goods_size'=>$sizeValue, 'goods_id'=>$goodsId));
                        if(!$extendGoods) {
                            $this->getServiceLocator()->get('frontHelper')->delCartSession($cartKey);
                            continue;
                        }
                        $goodsInfo->goods_stock      = $extendGoods->goods_extend_stock;
                        $goodsInfo->goods_item       = $extendGoods->goods_extend_item;
                        $goodsInfo->goods_shop_price = ($goodsInfo->goods_preferential_price <= 0 ? $extendGoods->goods_extend_price : $goodsInfo->goods_preferential_price);
                        $goodsInfo->goods_color      = $extendGoods->goods_color;
                        $goodsInfo->goods_size       = $extendGoods->goods_size;

                        //当未开启优惠价，判断是否有会员价
                        if($goodsInfo->goods_preferential_price <= 0 and $userGroupId > 0) {
                            $userGroupPrice = $this->getDbshopTable('GoodsUsergroupPriceTable')->infoGoodsUsergroupPrice(array('goods_id'=>$goodsInfo->goods_id, 'user_group_id'=>$userGroupId, 'goods_color'=>$colorValue, 'goods_size'=>$sizeValue));
                            if(isset($userGroupPrice->goods_user_group_price) and $userGroupPrice->goods_user_group_price > 0) $goodsInfo->goods_shop_price = $userGroupPrice->goods_user_group_price;
                        }
                    }

                } else {//当颜色或者尺寸存在其中一情况下，将其中值赋值入基础商品中
                    //判断是否有优惠价格存在
                    $goodsInfo->goods_shop_price = ($goodsInfo->goods_preferential_price <= 0 ? $goodsInfo->goods_shop_price : $goodsInfo->goods_preferential_price);
                    //当未开启优惠价，判断是否有会员价
                    if($goodsInfo->goods_preferential_price <= 0 and $userGroupId > 0) {
                        $userGroupPrice = $this->getDbshopTable('GoodsUsergroupPriceTable')->infoGoodsUsergroupPrice(array('goods_id'=>$goodsInfo->goods_id, 'user_group_id'=>$userGroupId, 'goods_color'=>'', 'goods_size'=>'', 'adv_spec_tag_id'=>''));
                        if(isset($userGroupPrice->goods_user_group_price) and $userGroupPrice->goods_user_group_price > 0) $goodsInfo->goods_shop_price = $userGroupPrice->goods_user_group_price;
                    }
                }
            }
            //当价格有所改变时，及时修改购物车中的商品价格
            if($goodsInfo->goods_shop_price > 0 and $goodsInfo->goods_shop_price != $goodsValue['goods_shop_price']) {
                $this->getServiceLocator()->get('frontHelper')->editCartSession($cartKey, 'goods_shop_price', $goodsInfo->goods_shop_price);
            }
        }
    }
    /**
     * 检查购物车是否全部为虚拟商品，如果是返回true，如果不是返回false
     * @param $cart
     * @return bool
     */
    private function checkCartVirtualGoods($cart)
    {
        $state = true;
        if(is_array($cart) and !empty($cart)) {
            foreach($cart as $value) {
                if($value['goods_type'] == 1) $state = false;
            }
        }
        return $state;
    }
    /**
     * 判断会员是否已经登录或者已经被删除
     */
    private function checkUserLoginOrDelete()
    {
        $userId = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');
        //判断是否登录
        if($userId == '') return $this->redirect()->toRoute('m_user/default',array('action'=>'login'));

        //判断该用户是否在登录后，后台被管理员删除
        $userInfo = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$userId));
        if($userInfo == null) {
            $userSession = new \Zend\Session\Container();
            $userSession->getManager()->getStorage()->clear('user_info');

            return $this->redirect()->toRoute('m_user/default', array('action'=>'login'));
        }
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