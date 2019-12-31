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

namespace Goods\Controller;

use Admin\Controller\BaseController;
use Zend\Config\Writer\Ini;

class CouponController extends BaseController
{
    /**
     * 优惠券列表
     * @return array
     */
    public function indexAction()
    {
        $array = array();

        $array['coupon_list'] = $this->getDbshopTable()->listCoupon();

        return $array;
    }
    /**
     * 添加优惠券
     * @return array|\Zend\Http\Response
     */
    public function addAction()
    {
        $array = array();

        if($this->request->isPost()) {
            $couponArray = $this->request->getPost()->toArray();

            if(in_array($couponArray['get_coupon_type'], array('click', 'buy'))) {
                if($couponArray['get_user_type'] == 'user_group') $couponArray['get_user_group'] = serialize($couponArray['get_user_group']);
                else $couponArray['get_user_group'] = '';

                if($couponArray['get_goods_type'] == 'class_goods') $couponArray['get_coupon_goods_body'] = serialize($couponArray['get_coupon_goods_class']);
                elseif ($couponArray['get_goods_type'] == 'brand_goods') $couponArray['get_coupon_goods_body'] = serialize($couponArray['get_coupon_goods_class']);
                else $couponArray['get_coupon_goods_body'] = '';
            } else {
                $couponArray['get_user_type'] = 'all_user';
                $couponArray['get_user_group'] = '';
                $couponArray['get_goods_type'] = 'all_goods';
                $couponArray['get_coupon_goods_body'] = '';
            }

            if($couponArray['coupon_goods_type'] == 'all_goods') {
                $couponArray['coupon_goods_body'] = '';
            } else {
                if($couponArray['coupon_goods_type'] == 'class_goods') $couponArray['coupon_goods_body'] = serialize($couponArray['class_id']);
                if($couponArray['coupon_goods_type'] == 'brand_goods') $couponArray['coupon_goods_body'] = serialize($couponArray['brand_id']);
                if($couponArray['coupon_goods_type'] == 'individual_goods') {
                    $couponArray['coupon_goods_id'] = array_unique($couponArray['coupon_goods_id']);
                    $couponArray['coupon_goods_body'] = serialize($couponArray['coupon_goods_id']);
                }
            }
            $this->getDbshopTable()->addCoupon($couponArray);
            $this->createCouponRule();
            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('优惠券'), 'operlog_info'=>$this->getDbshopLang()->translate('添加优惠券') . '&nbsp;' . $couponArray['coupon_name']));

            unset($couponArray);
            return $this->redirect()->toRoute('coupon/default');

        }

        //会员分组
        $array['group_array'] = $this->getDbshopTable('UserGroupTable')->listUserGroup(array('language'=>$this->getDbshopLang()->getLocale()));
        //商品分类
        $array['goods_class']  = $this->getDbshopTable('GoodsClassTable')->classOptions(0,$this->getDbshopTable('GoodsClassTable')->listGoodsClass());
        //商品品牌
        $array['goods_brand'] = $this->getDbshopTable('GoodsBrandTable')->listGoodsBrand();

        return $array;
    }
    /**
     * 编辑优惠券
     * @return array|\Zend\Http\Response
     */
    public function editAction()
    {
        $couponId = (int) $this->params('id', 0);
        if($couponId <= 0) return $this->redirect()->toRoute('coupon/default');

        $array = array();

        if($this->request->isPost()) {
            $couponArray = $this->request->getPost()->toArray();

            if(in_array($couponArray['get_coupon_type'], array('click', 'buy'))) {
                if($couponArray['get_user_type'] == 'user_group') $couponArray['get_user_group'] = serialize($couponArray['get_user_group']);
                else $couponArray['get_user_group'] = '';

                if($couponArray['get_goods_type'] == 'class_goods') $couponArray['get_coupon_goods_body'] = serialize($couponArray['get_coupon_goods_class']);
                elseif ($couponArray['get_goods_type'] == 'brand_goods') $couponArray['get_coupon_goods_body'] = serialize($couponArray['get_coupon_goods_class']);
                else $couponArray['get_coupon_goods_body'] = '';
            } else {
                $couponArray['get_user_type'] = 'all_user';
                $couponArray['get_user_group'] = '';
                $couponArray['get_goods_type'] = 'all_goods';
                $couponArray['get_coupon_goods_body'] = '';
            }

            if($couponArray['coupon_goods_type'] == 'all_goods') {
                $couponArray['coupon_goods_body'] = '';
            } else {
                if($couponArray['coupon_goods_type'] == 'class_goods') $couponArray['coupon_goods_body'] = serialize($couponArray['class_id']);
                if($couponArray['coupon_goods_type'] == 'brand_goods') $couponArray['coupon_goods_body'] = serialize($couponArray['brand_id']);
                if($couponArray['coupon_goods_type'] == 'individual_goods') {
                    $couponArray['coupon_goods_id'] = array_unique($couponArray['coupon_goods_id']);
                    $couponArray['coupon_goods_body'] = serialize($couponArray['coupon_goods_id']);
                }

            }
            $this->getDbshopTable()->updateCoupon($couponArray, array('coupon_id'=>$couponId));
            $this->createCouponRule();
            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('优惠券'), 'operlog_info'=>$this->getDbshopLang()->translate('编辑优惠券') . '&nbsp;' . $couponArray['coupon_name']));

            unset($couponArray);
            return $this->redirect()->toRoute('coupon/default');
        }

        $array['coupon_info'] = $this->getDbshopTable()->infoCoupon(array('coupon_id'=>$couponId));
        //会员分组
        $array['group_array'] = $this->getDbshopTable('UserGroupTable')->listUserGroup(array('language'=>$this->getDbshopLang()->getLocale()));
        //商品分类
        $array['goods_class']  = $this->getDbshopTable('GoodsClassTable')->classOptions(0,$this->getDbshopTable('GoodsClassTable')->listGoodsClass());
        //商品品牌
        $array['goods_brand'] = $this->getDbshopTable('GoodsBrandTable')->listGoodsBrand();

        //如果是个别商品这里需要处理
        if($array['coupon_info']->coupon_goods_type == 'individual_goods' and !empty($array['coupon_info']->coupon_goods_body)) {
            $goodsIdArray = unserialize($array['coupon_info']->coupon_goods_body);
            if(!empty($goodsIdArray)) {
                $array['goods_list'] = $this->getDbshopTable('GoodsTable')->listGoods(array('dbshop_goods.goods_id IN ('.implode(',', $goodsIdArray).')'));
            }
        }

        return $array;
    }
    /**
     * 删除优惠券规则
     */
    public function delAction()
    {
        $couponId = (int) $this->request->getPost('coupon_id', 0);
        if($couponId <= 0) exit($this->getDbshopLang()->translate('该优惠券不存在！'));

        $couponInfo = $this->getDbshopTable()->infoCoupon(array('coupon_id'=>$couponId));
        if(empty($couponInfo)) exit($this->getDbshopLang()->translate('该优惠券不存在！'));

        $userCoupon = $this->getDbshopTable('UserCouponTable')->listUserCoupon(array('coupon_use_state IN (0,1) and coupon_id='.$couponId));
        if(!empty($userCoupon)) exit($this->getDbshopLang()->translate('存在未使用的优惠券，无法删除！'));

        $this->getDbshopTable()->delCoupon(array('coupon_id'=>$couponId));
        $this->createCouponRule();
        //记录操作日志
        $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('优惠券'), 'operlog_info'=>$this->getDbshopLang()->translate('删除优惠券') . '&nbsp;' . $couponInfo->coupon_name));
        exit('true');
    }
    /**
     * 会员优惠券使用情况
     * @return array
     */
    public function userCouponAction()
    {
        $couponId = (int) $this->params('id', 0);
        if($couponId <= 0)  return $this->redirect()->toRoute('coupon/default');

        $couponInfo = $this->getDbshopTable()->infoCoupon(array('coupon_id'=>$couponId));
        if(empty($couponInfo))  return $this->redirect()->toRoute('coupon/default');

        $array = array();

        $array['coupon_info'] = $couponInfo;

        //优惠券分页
        $page = $this->params('page',1);
        $array['page']     = $page;
        $array['user_coupon_list'] = $this->getDbshopTable('UserCouponTable')->listPageUserCoupon(array('page'=>$page, 'page_num'=>20), array('user_coupon.coupon_id'=>$couponId));

        return $array;
    }
    /**
     * 检索返回单个商品信息
     */
    public function oneGoodsAction()
    {
        $goodsId = (int) $this->request->getPost('goods_id');
        if($goodsId > 0) {
            $goodsInfo = $this->getDbshopTable('GoodsTable')->infoGoods(array('dbshop_goods.goods_id'=>$goodsId,'e.language'=>$this->getDbshopLang()->getLocale()));
            if(!empty($goodsInfo)) {
                exit(json_encode(array(
                    'state'=>'true',
                    'goods_id'=>$goodsId,
                    'goods_item'=>$goodsInfo->goods_item,
                    'goods_name'=>$goodsInfo->goods_name,
                    'goods_shop_price'=>$goodsInfo->goods_shop_price,
                    'goods_state'=>$goodsInfo->goods_state == 1 ? $this->getDbshopLang()->translate('上架') : $this->getDbshopLang()->translate('下架')
                )));
            }
        }
        exit(json_encode(array('state'=>'false')));
    }
    /**
     * 生成优惠券ini文件
     * @throws \Exception
     */
    private function createCouponRule()
    {
        $couponRuleArray = array();
        $writeIni = new Ini();
        $couponArray = $this->getDbshopTable()->couponArray(array('coupon_state'=>1));
        if(is_array($couponArray) and !empty($couponArray)) {
            foreach($couponArray as $key => $value) {
                $value['coupon_name']           = str_replace('"', "'", $value['coupon_name']);
                $value['coupon_info']           = str_replace('"', "'", $value['coupon_info']);
                $value['get_user_group']        = (!empty($value['get_user_group']) ? unserialize($value['get_user_group']) : array());
                $value['get_coupon_goods_body'] = (!empty($value['get_coupon_goods_body']) ? unserialize($value['get_coupon_goods_body']) : array());
                $value['coupon_goods_body']     = (!empty($value['coupon_goods_body']) ? unserialize($value['coupon_goods_body']) : array());
                $value['coupon_use_channel']    = !empty($value['coupon_use_channel']) ? unserialize($value['coupon_use_channel']) : array();
                $couponRuleArray[$key] = $value;
            }
        }

        $writeIni->toFile(DBSHOP_PATH . '/data/moduledata/System/coupon_rule.ini', $couponRuleArray);
    }
    /**
     * 数据表调用
     * @param string $tableName
     * @return multitype:
     */
    private function getDbshopTable ($tableName='CouponTable')
    {
        if (empty($this->dbTables[$tableName])) {
            $this->dbTables[$tableName] = $this->getServiceLocator()->get($tableName);
        }
        return $this->dbTables[$tableName];
    }
}