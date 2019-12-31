<?php
/**
 * DBShop 电子商务系统
 *
 * ==========================================================================
 * @link      http://www.dbshop.net/
 * @copyright Copyright (c) 2012-2017 DBShop.net Inc. (http://www.dbshop.net)
 * @license   http://www.dbshop.net/license.html License
 * ==========================================================================
 *
 * @author    静静的风
 *
 */

namespace Goods\Service;

use Zend\Config\Reader\Ini;
/**
 * 获取用户可以使用的优惠券
 * Class CouponRuleService
 * @package Goods\Service
 */
class CouponRuleService
{
    public static function couponUseRule(array $couponData, $e)
    {
        $couponRule = self::getCouponRuleIni();
        $couponArray= array();
        $couponId   = array();

        if(empty($couponRule)) return $couponArray;
        if(!is_array($couponData['cartGoods']) or empty($couponData['cartGoods'])) return $couponArray;

        //在规则中匹配符合要求的优惠券
        foreach ($couponRule as $ruleKey => $ruleValue) {
            if($ruleValue['coupon_state'] == '2') continue;
            if(!empty($ruleValue['coupon_start_time']) and !empty($ruleValue['coupon_end_time']) and $ruleValue['coupon_start_time']>$ruleValue['coupon_end_time']) continue;
            if(!empty($ruleValue['coupon_start_time']) and $ruleValue['coupon_start_time'] > time()) continue;
            if(!empty($ruleValue['coupon_end_time']) and $ruleValue['coupon_end_time'] < time()) continue;

            //使用渠道的判定
            /*if(isset($ruleValue['coupon_use_channel']) and !empty($ruleValue['coupon_use_channel']) and isset($couponData['use_channel']) and !empty($couponData['use_channel'])) {
                if(!in_array($couponData['use_channel'], $ruleValue['coupon_use_channel'])) continue;
            }*/

            $costTotal = 0;
            if($ruleValue['coupon_goods_type'] == 'all_goods') {
                foreach($couponData['cartGoods'] as $cartValue) {
                    $costTotal = $costTotal + $cartValue['goods_shop_price'] * $cartValue['buy_num'];
                }
            }
            if($ruleValue['coupon_goods_type'] == 'class_goods') {
                if(empty($ruleValue['coupon_goods_body'])) continue;
                foreach($couponData['cartGoods'] as $cartValue) {
                    foreach ($cartValue['class_id_array'] as $class_id) {
                        if(in_array($class_id, $ruleValue['coupon_goods_body'])) {
                            $costTotal = $costTotal + $cartValue['goods_shop_price'] * $cartValue['buy_num'];
                            continue 2;
                        }
                    }
                }
            }
            if($ruleValue['coupon_goods_type'] == 'brand_goods') {
                if(empty($ruleValue['coupon_goods_body']))  continue;
                foreach($couponData['cartGoods'] as $cartValue) {
                    if(in_array($cartValue['brand_id'], $ruleValue['coupon_goods_body'])) {
                        $costTotal = $costTotal + $cartValue['goods_shop_price'] * $cartValue['buy_num'];
                        continue;
                    }
                }
            }
            if($ruleValue['coupon_goods_type'] == 'individual_goods') {
                if(empty($ruleValue['coupon_goods_body'])) continue;
                $individualState = false;
                foreach($couponData['cartGoods'] as $cartValue) {
                    if(in_array($cartValue['goods_id'], $ruleValue['coupon_goods_body'])) $individualState = true;

                    $costTotal = $costTotal + $cartValue['goods_shop_price'] * $cartValue['buy_num'];
                }
                if(!$individualState) continue;
            }

            if($costTotal >= $ruleValue['shopping_amount']) {
                $couponId[] = $ruleValue['coupon_id'];

                //此操作为使用优惠券获得优惠值时使用，需要添加 type、coupon_id 参数
                if(isset($couponData['type']) and $couponData['type'] == 'submit_cart' and $couponData['coupon_id'] == $ruleValue['coupon_id']) {
                    return $ruleValue['shopping_discount'];
                }
            }
        }
        //当为使用优惠券获得优惠值使用时，没有任何匹配，则直接输出0
        if(isset($couponData['type']) and $couponData['type'] == 'submit_cart') return 0;

        if(empty($couponId)) return $couponArray;

        //查看当前用户获取到的优惠券中是否有符合条件的
        $where = 'dbshop_user_coupon.coupon_id IN ('.implode(',', $couponId).')';
        $where .= ' and dbshop_user_coupon.user_id='.$couponData['user_id'];
        $where .= ' and dbshop_user_coupon.coupon_use_state=1';
        $userCouponTable = $e->getServiceLocator()->get('UserCouponTable');
        $couponArray = $userCouponTable->useUserCoupon(array($where));

        return $couponArray;
    }
    /**
     * 获取优惠规则
     * @return array
     */
    public static function getCouponRuleIni()
    {
        $iniReader  = new Ini();
        $couponRule = array();

        $iniPath    = DBSHOP_PATH . '/data/moduledata/System/coupon_rule.ini';
        if(file_exists($iniPath)) {
            $couponRule = $iniReader->fromFile($iniPath);
        }

        return $couponRule;
    }
}