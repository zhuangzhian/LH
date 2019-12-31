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

namespace Shopfront\Controller;

use Zend\View\Model\ViewModel;

class HomecouponController extends FronthomeController
{
    private $dbTables = array();
    private $translator;

    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTemplate('/shopfront/home/coupon.phtml');

        $array = array();

        //优惠券分页
        $state = $this->request->getQuery('state');
        if(!in_array($state, array('all', '0', '1', '2', '3'))) $state = 'all';
        $page = $this->params('page',1);
        $array['page']  = $page;
        $array['state'] = $state;
        $where['user_id']= $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');
        if($state != 'all') $where['coupon_use_state'] = $state;
        $array['user_coupon_list'] = $this->getDbshopTable('UserCouponTable')->listPageUserCoupon(array('page'=>$page, 'page_num'=>20), $where);

        //优惠券统计信息
        $array['coupon_num'] = $this->getDbshopTable('UserCouponTable')->allStateNumCoupon($where['user_id']);

        $view->setVariables($array);
        return $view;
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