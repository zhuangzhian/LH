<?php
/**
 * DBShop 电子商务系统
 *
 * ==========================================================================
 * @link      http://www.dbshop.net/
 * @copyright Copyright (c) 2012-2016 DBShop.net Inc. (http://www.dbshop.net)
 * @license   http://www.dbshop.net/license.html License
 * ==========================================================================
 *
 * @author    静静的风
 *
 */
namespace Analytics\Controller;

use Admin\Controller\BaseController;

class AnalyticsController extends BaseController
{
    private $loginUlr   = 'https://api.baidu.com/sem/common/HolmesLoginService';
    private $apiUlr     = 'https://api.baidu.com/json/tongji/v1/ReportService';
    private $uUid       = '666666';
    private $accountType= 1;

    private $userName   = '';
    private $userPasswd = '';
    private $token      = '';
    private $siteId     = '';
    private $headers    = array();
    private $publicKey;

    /**
     * 客户统计
     * @return array
     */
    public function userStatsAction()
    {
        $array = array();

        $array['page_type'] = 'userStats';//页面类型，用于标识页面左侧的菜单
        //当天客户数
        $dayTime = mktime(0,0,0,date('m'),date('d'),date('Y'));
        $array['day_user_total'] = $this->getDbshopTable('UserTable')->countUser(array('user_time>'.$dayTime));
        //当月客户数
        $monthTime = mktime(0,0,0,date('m'),1,date('Y'));
        $array['month_user_total'] = $this->getDbshopTable('UserTable')->countUser(array('user_time>'.$monthTime));
        //客户总数
        $array['user_total'] = $this->getDbshopTable('UserTable')->countUser();
        //购买过订单的客户数
        $array['user_buyer_total'] = $this->getDbshopTable('OrderTable')->buyerCountOrder();

        //统计图表输出
        $dateNum = (int) $this->request->getQuery('dateNum');
        if($dateNum <= 0) $dateNum = 7 - 1;
        else $dateNum = $dateNum - 1;//因为是从0开始算，所以减去1
        $dateTime = strtotime("-".$dateNum." day");
        $dateTime = strtotime(date('Y-m-d 00:00:00', $dateTime));

        $array['date_num'] = $dateNum;
        $userWhere = 'user_time>'.$dateTime;
        $orderWhere= 'order_time>'.$dateTime;

        //当POST传过时间值时
        if($this->request->isPost()) {
            $postArray = $this->request->getPost()->toArray();
            if(!empty($postArray['start_time']) and !empty($postArray['end_time'])) {
                $startTime  = strtotime($postArray['start_time']);
                $endTime    = strtotime($postArray['end_time'].' 24:00:00');
                $dateNum    = round(($endTime - $startTime)/(60*60*24))-1;
                $array['date_num']  = '';
                $array['start_time']= $postArray['start_time'];
                $array['end_time']  = $postArray['end_time'];
                $userWhere = 'user_time>='.$startTime.' and user_time<='.$endTime;
                $orderWhere= 'order_time>='.$startTime.' and order_time<='.$endTime;
            }

        }

        $userS = $this->getDbshopTable('UserTable')->StatsUser($userWhere, '', 'utime');
        $userArray = array();
        if(is_array($userS) and !empty($userS)) {
            foreach($userS as $value) {
                $userArray[$value['utime']] = $value['user_count'];
            }
        }
        $orderS = $this->getDbshopTable('OrderTable')->StatsOrder($orderWhere, '', 'otime');
        $orderArray = array();
        if(is_array($orderS) and !empty($orderS)) {
            foreach($orderS as $oValue) {
                $orderArray[$oValue['otime']] = $oValue['order_count'];
            }
        }

        $dateArray = array();
        $uArray    = array();
        $oArray    = array();
        for($i=$dateNum; $i>=0; $i--) {
            //$dateStr = date("Y-m-d", strtotime("-".$i." day"));
            $dateStr = (isset($endTime) and !empty($endTime)) ? date("Y-m-d", strtotime("-".$i." day", ($endTime-60*60*24))) : date("Y-m-d", strtotime("-".$i." day"));
            $dateArray[] = '\''.$dateStr.'\'';
            $uArray[$dateStr] = isset($userArray[$dateStr]) ? $userArray[$dateStr] : 0;
            $oArray[$dateStr] = isset($orderArray[$dateStr]) ? $orderArray[$dateStr] : 0;
        }
        $array['week_user']= implode(',', $uArray);
        $array['week_order']= implode(',', $oArray);
        $array['date_str'] = implode(',', $dateArray);

        return $array;
    }
    /**
     * 订单统计
     * @return array
     */
    public function orderStatsAction()
    {
        $array = array();

        $array['page_type'] = 'orderStats';//页面类型，用于标识页面左侧的菜单

        //当天订单数
        $dayTime = mktime(0,0,0,date('m'),date('d'),date('Y'));
        $array['day_order_num'] = $this->getDbshopTable('OrderTable')->stateCountOrder(array('order_time>'.$dayTime));
        //当月订单数
        $monthTime = mktime(0,0,0,date('m'),1,date('Y'));
        $array['month_order_num'] = $this->getDbshopTable('OrderTable')->stateCountOrder(array('order_time>'.$monthTime));
        //订单总数
        $array['order_num'] = $this->getDbshopTable('OrderTable')->stateCountOrder(array());
        //已付款订单数
        $array['pay_order_num'] = $this->getDbshopTable('OrderTable')->stateCountOrder(array("(pay_code<>'xxfk' and pay_code<>'hdfk' and order_state>=20) or order_state=60"));

        //统计图表输出
        $dateNum = (int) $this->request->getQuery('dateNum');
        if($dateNum <= 0) $dateNum = 7 - 1;
        else $dateNum = $dateNum - 1;//因为是从0开始算，所以减去1
        $dateTime = strtotime("-".$dateNum." day");
        $dateTime = strtotime(date('Y-m-d 00:00:00', $dateTime));

        $array['date_num'] = $dateNum;
        $orderWhere= 'order_time>'.$dateTime;

        //当GET传过时间值时
        if($this->request->isGet()) {
            $postArray = $this->request->getQuery()->toArray();
            if(!empty($postArray['start_time']) and !empty($postArray['end_time'])) {
                $startTime  = strtotime($postArray['start_time']);
                $endTime    = strtotime($postArray['end_time'].' 24:00:00');
                $dateNum    = round(($endTime - $startTime)/(60*60*24))-1;
                $array['date_num']  = '';
                $array['start_time']= $postArray['start_time'];
                $array['end_time']  = $postArray['end_time'];
                $orderWhere= 'order_time>='.$startTime.' and order_time<='.$endTime;
            }
        }

        //当GET传递客户组时
        $getArray['group_id'] = (int) $this->request->getQuery('group_id');
        if($getArray['group_id'] > 0) {
            $array['group_id'] = $getArray['group_id'];
            $orderWhere .= empty($orderWhere) ? 'u.group_id='.$getArray['group_id'] : ' and u.group_id='.$getArray['group_id'];
        }

        $payOrderS = $this->getDbshopTable('OrderTable')->StatsOrder($orderWhere." and ((pay_code<>'xxfk' and pay_code<>'hdfk' and order_state>=20) or order_state=60)", '', 'otime');
        $payOrderArray = array();
        if(is_array($payOrderS) and !empty($payOrderS)) {
            foreach($payOrderS as $value) {
                $payOrderArray[$value['otime']] = $value['order_count'];
            }
        }
        $orderS = $this->getDbshopTable('OrderTable')->StatsOrder($orderWhere, '', 'otime');
        $orderArray = array();
        if(is_array($orderS) and !empty($orderS)) {
            foreach($orderS as $oValue) {
                $orderArray[$oValue['otime']] = $oValue['order_count'];
            }
        }

        $dateArray = array();
        $pArray    = array();
        $oArray    = array();
        for($i=$dateNum; $i>=0; $i--) {
            //$dateStr = date("Y-m-d", strtotime("-".$i." day"));
            $dateStr = (isset($endTime) and !empty($endTime)) ? date("Y-m-d", strtotime("-".$i." day", ($endTime-60*60*24))) : date("Y-m-d", strtotime("-".$i." day"));
            $dateArray[] = '\''.$dateStr.'\'';
            $pArray[$dateStr] = isset($payOrderArray[$dateStr]) ? $payOrderArray[$dateStr] : 0;
            $oArray[$dateStr] = isset($orderArray[$dateStr])    ? $orderArray[$dateStr]     : 0;
        }
        $array['week_pay_order']= implode(',', $pArray);
        $array['week_order']    = implode(',', $oArray);
        $array['date_str']      = implode(',', $dateArray);

        //支付方式
        $paymentArray = array();
        $filePath      = DBSHOP_PATH . '/data/moduledata/Payment/';
        if(is_dir($filePath)) {
            $dh = opendir($filePath);
            while (false !== ($fileName = readdir($dh))) {
                if($fileName != '.' and $fileName != '..' and stripos($fileName, '.php') !== false and $fileName != '.DS_Store') {
                    $paymentInfo = include ($filePath . $fileName);
                    $paymentArray[$paymentInfo['editaction']] = '\''.$paymentInfo['payment_name']['content'].'\'';
                }
            }
        }
        $array['payment_str'] = implode(',', $paymentArray);
        $lPayment = $this->getDbshopTable('OrderTable')->statsOrderPaymentOrExpress('order_state>0 and '.$orderWhere, '', 'pay_code');
        if(is_array($lPayment) and !empty($lPayment)) {
            foreach($lPayment as $lValue) {
                $paymentArray[$lValue['pay_code']] = array('name'=>$paymentArray[$lValue['pay_code']], 'num'=>$lValue['order_count']);
            }
        }
        $array['payment_array'] = $paymentArray;

        //配送方式
        $lExpressArray= array();
        $expressArray = $this->getDbshopTable('ExpressTable')->listExpress(array());
        if(is_array($expressArray) and !empty($expressArray)) {
            foreach($expressArray as $eValue) {
                $lExpressArray[$eValue['express_id']] = '\''.$eValue['express_name'].'\'';
            }
        }
        $array['express_str'] = !empty($lExpressArray) ? implode(',', $lExpressArray) : '';

        $lExpress = $this->getDbshopTable('OrderTable')->statsOrderPaymentOrExpress('(express_name is not null) and order_state>0 and '.$orderWhere, '', 'express_id', 'express_name AS express_name, express_id AS express_id');
        if(is_array($lExpress) and !empty($lExpress)) {
            foreach($lExpress as $lEValue) {
                $lExpressArray[$lEValue['express_id']] = array('name'=>$lExpressArray[$lEValue['express_id']], 'num'=>$lEValue['order_count']);
            }
        }
        $array['express_array'] = $lExpressArray;

        //订单地区
        $regionArray = $this->getDbshopTable('OrderDeliveryAddressTable')->statsDelivery('order_state>0 and '.$orderWhere);
        $array['region_array'] = $regionArray;

        //客户的等级
        $array['group_array'] = $this->getDbshopTable('UserGroupTable')->listUserGroup();

        return $array;
    }
    /**
     * 销售概况
     * @return array
     */
    public function saleStatsAction()
    {
        $array = array();

        $array['page_type'] = 'saleStats';//页面类型，用于标识页面左侧的菜单

        //客户的等级
        $array['group_array'] = $this->getDbshopTable('UserGroupTable')->listUserGroup();

        //订单总额
        $array['order_total']       = $this->getDbshopTable('OrderTable')->StatsOrder("order_state>0 and refund_state<>1", '', '', 'SUM(order_amount) AS order_total');
        //未付款总额
        $array['order_d_pay_total'] = $this->getDbshopTable('OrderTable')->StatsOrder("((order_state>=0 and order_state<20) or (order_state>=20 and (pay_code='xxfk' or pay_code='hdfk'))) and refund_state<>1", '', '', 'SUM(order_amount) AS order_total');
        //已付款总额
        $array['order_pay_total']   = $this->getDbshopTable('OrderTable')->StatsOrder("((pay_code<>'xxfk' and pay_code<>'hdfk' and order_state>=20) or order_state=60) and refund_state<>1", '', '', 'SUM(order_amount) AS order_total');

        $array['get_array'] = array();//用户输出query在订单走势与销售走势间切换
        $saleType = trim($this->request->getQuery('sale_type'));
        if(isset($saleType) and $saleType == 'total') $saleType = 'total';
        else $saleType = 'num';

        //统计图表输出
        $dateNum = (int) $this->request->getQuery('dateNum');
        if($dateNum <= 0) $dateNum = 7 - 1;
        else $dateNum = $dateNum - 1;//因为是从0开始算，所以减去1
        $dateTime = strtotime("-".$dateNum." day");
        $dateTime = strtotime(date('Y-m-d 00:00:00', $dateTime));

        $array['date_num'] = $dateNum;
        $orderWhere= 'order_time>'.$dateTime;
        $array['get_array']['dateNum'] = $dateNum+1;

        //当GET传过时间值时
        $getArray['start_time'] = $this->request->getQuery('start_time');
        $getArray['end_time']   = $this->request->getQuery('end_time');
        if(!empty($getArray['start_time']) and !empty($getArray['end_time'])) {
            $startTime  = strtotime($getArray['start_time']);
            $endTime    = strtotime($getArray['end_time'].' 24:00:00');
            $dateNum    = round(($endTime - $startTime)/(60*60*24))-1;
            $array['date_num']  = '';
            $array['start_time']= $getArray['start_time'];
            $array['end_time']  = $getArray['end_time'];
            $orderWhere= 'order_time>='.$startTime.' and order_time<='.$endTime;

            $array['get_array']['start_time']   = $getArray['start_time'];
            $array['get_array']['end_time']     = $getArray['end_time'] ;
        }

        //当GET传递客户组时
        $getArray['group_id'] = (int) $this->request->getQuery('group_id');
        if($getArray['group_id'] > 0) {
            $array['group_id'] = $getArray['group_id'];
            $orderWhere .= empty($orderWhere) ? 'u.group_id='.$getArray['group_id'] : ' and u.group_id='.$getArray['group_id'];
        }

        //当前订单总额
        $array['d_order_total']       = $this->getDbshopTable('OrderTable')->StatsOrder($orderWhere." and order_state>0 and refund_state<>1", '', '', 'SUM(order_amount) AS order_total');
        //当前未付款总额
        $array['d_order_d_pay_total'] = $this->getDbshopTable('OrderTable')->StatsOrder($orderWhere." and ((order_state>=0 and order_state<20) or (order_state>=20 and (pay_code='xxfk' or pay_code='hdfk'))) and refund_state<>1", '', '', 'SUM(order_amount) AS order_total');
        //当前已付款总额
        $array['d_order_pay_total']   = $this->getDbshopTable('OrderTable')->StatsOrder($orderWhere." and ((pay_code<>'xxfk' and pay_code<>'hdfk' and order_state>=20) or order_state=60) and refund_state<>1", '', '', 'SUM(order_amount) AS order_total');


        if($saleType == 'num') {//订单走势
            $payOrderS = $this->getDbshopTable('OrderTable')->StatsOrder($orderWhere." and refund_state<>1 and ((pay_code<>'xxfk' and pay_code<>'hdfk' and order_state>=20) or order_state=60)", '', 'otime');
            $payOrderArray = array();
            if(is_array($payOrderS) and !empty($payOrderS)) {
                foreach($payOrderS as $value) {
                    $payOrderArray[$value['otime']] = $value['order_count'];
                }
            }
            $dPayOrderS = $this->getDbshopTable('OrderTable')->StatsOrder($orderWhere." and refund_state<>1 and ((order_state>=0 and order_state<20) or (order_state>20 and (pay_code='xxfk' or pay_code='hdfk')))", '', 'otime');
            $dOrderArray = array();
            if(is_array($dPayOrderS) and !empty($dPayOrderS)) {
                foreach($dPayOrderS as $dValue) {
                    $dOrderArray[$dValue['otime']] = $dValue['order_count'];
                }
            }
        } else {//销售额走势
            $payOrderTotal = $this->getDbshopTable('OrderTable')->StatsOrder($orderWhere." and refund_state<>1 and ((pay_code<>'xxfk' and pay_code<>'hdfk' and order_state>=20) or order_state=60)", '', 'otime', 'SUM(order_amount) AS order_total');
            $payOrderTotalArray = array();
            if(is_array($payOrderTotal) and !empty($payOrderTotal)) {
                foreach($payOrderTotal as $value) {
                    $payOrderTotalArray[$value['otime']] = number_format($value['order_total'], 2, '.', '');
                }
            }
            $dPayOrderTotal = $this->getDbshopTable('OrderTable')->StatsOrder($orderWhere." and refund_state<>1 and ((order_state>=0 and order_state<20) or (order_state>=20 and (pay_code='xxfk' or pay_code='hdfk')))", '', 'otime', 'SUM(order_amount) AS order_total');
            $dOrderTotalArray = array();
            if(is_array($dPayOrderTotal) and !empty($dPayOrderTotal)) {
                foreach($dPayOrderTotal as $dValue) {
                    $dOrderTotalArray[$dValue['otime']] = number_format($dValue['order_total'], 2, '.', '');
                }
            }
        }

        $dateArray = array();
        $pArray    = array();
        $dArray    = array();
        $zArray    = array();
        $pTArray   = array();
        $dTArray   = array();
        $zTArray   = array();
        for($i=$dateNum; $i>=0; $i--) {
            //$dateStr = date("Y-m-d", strtotime("-".$i." day"));
            $dateStr = (isset($endTime) and !empty($endTime)) ? date("Y-m-d", strtotime("-".$i." day", ($endTime-60*60*24))) : date("Y-m-d", strtotime("-".$i." day"));
            $dateArray[] = '\''.$dateStr.'\'';
            if($saleType == 'num') {
                $pArray[$dateStr]   = isset($payOrderArray[$dateStr])       ? $payOrderArray[$dateStr]      : 0;
                $dArray[$dateStr]   = isset($dOrderArray[$dateStr])         ? $dOrderArray[$dateStr]        : 0;
                $zArray[$dateStr]   = $pArray[$dateStr] + $dArray[$dateStr];
            } else {
                $pTArray[$dateStr]  = isset($payOrderTotalArray[$dateStr])  ? $payOrderTotalArray[$dateStr] : 0;
                $dTArray[$dateStr]  = isset($dOrderTotalArray[$dateStr])    ? $dOrderTotalArray[$dateStr]   : 0;
                $zTArray[$dateStr]  = $pTArray[$dateStr] + $dTArray[$dateStr];
            }
        }
        if($saleType == 'num') {
            $array['pay_order']     = implode(',', $pArray);
            $array['d_pay_order']   = implode(',', $dArray);
            $array['z_pay_order']   = implode(',', $zArray);
        } else {
            $array['p_total_order'] = implode(',', $pTArray);
            $array['d_total_order'] = implode(',', $dTArray);
            $array['z_total_order'] = implode(',', $zTArray);
        }
        $array['date_str']      = implode(',', $dateArray);
        $array['sale_type']     = $saleType;
        return $array;
    }
    /**
     * 会员排行
     * @return array
     */
    public function usersOrderAction()
    {
        $array = array();

        $array['page_type'] = 'usersOrder';//页面类型，用于标识页面左侧的菜单

        //时间判定
        $dateNum = (int) $this->request->getQuery('dateNum');
        if($dateNum <= 0) $dateNum = 7 - 1;
        else $dateNum = $dateNum - 1;//因为是从0开始算，所以减去1
        $dateTime = strtotime("-".$dateNum." day");
        $dateTime = strtotime(date('Y-m-d 00:00:00', $dateTime));

        $array['date_num'] = $dateNum;
        $orderWhere= 'o.order_time>'.$dateTime;

        //当GET传过时间值时
        $getArray['start_time'] = $this->request->getQuery('start_time');
        $getArray['end_time']   = $this->request->getQuery('end_time');
        if(!empty($getArray['start_time']) and !empty($getArray['end_time'])) {
            $startTime  = strtotime($getArray['start_time']);
            $endTime    = strtotime($getArray['end_time'].' 24:00:00');
            $dateNum    = round(($endTime - $startTime)/(60*60*24))-1;
            $array['date_num']  = '';
            $array['start_time']= $getArray['start_time'];
            $array['end_time']  = $getArray['end_time'];
            $orderWhere= 'o.order_time>='.$startTime.' and o.order_time<='.$endTime;

        }

        //当GET传递客户组时
        $where = '';
        $getArray['group_id'] = (int) $this->request->getQuery('group_id');
        if($getArray['group_id'] > 0) {
            $array['group_id'] = $getArray['group_id'];
            $where = 'ug.group_id='.$getArray['group_id'];
        }

        $page = $this->params('page',1);
        $pageNum    = 20;
        $array['user_list'] = $this->getDbshopTable('UserTable')->userRanking(array('page'=>$page, 'page_num'=>$pageNum), $where, 'and '.$orderWhere, 'order_count DESC');
        $array['page']      = $page;
        $array['page_base_num'] = $pageNum * ($page - 1);

        //客户的等级
        $array['group_array'] = $this->getDbshopTable('UserGroupTable')->listUserGroup();

        return $array;
    }
    /**
     * 销售明细
     * @return array
     */
    public function saleListAction()
    {
        $array = array();

        $array['page_type'] = 'saleList';//页面类型，用于标识页面左侧的菜单

        //时间判定
        $dateNum = (int) $this->request->getQuery('dateNum');
        if($dateNum <= 0) $dateNum = 7 - 1;
        else $dateNum = $dateNum - 1;//因为是从0开始算，所以减去1
        $dateTime = strtotime("-".$dateNum." day");
        $dateTime = strtotime(date('Y-m-d 00:00:00', $dateTime));

        $array['date_num'] = $dateNum;
        $orderWhere= 'dbshop_order.order_time>'.$dateTime;

        //当GET传过时间值时
        $getArray['start_time'] = $this->request->getQuery('start_time');
        $getArray['end_time']   = $this->request->getQuery('end_time');
        if(!empty($getArray['start_time']) and !empty($getArray['end_time'])) {
            $startTime  = strtotime($getArray['start_time']);
            $endTime    = strtotime($getArray['end_time'].' 24:00:00');
            $dateNum    = round(($endTime - $startTime)/(60*60*24))-1;
            $array['date_num']  = '';
            $array['start_time']= $getArray['start_time'];
            $array['end_time']  = $getArray['end_time'];
            $orderWhere= 'dbshop_order.order_time>='.$startTime.' and dbshop_order.order_time<='.$endTime;

        }
        //当GET传递客户组时
        $getArray['group_id'] = (int) $this->request->getQuery('group_id');
        if($getArray['group_id'] > 0) {
            $array['group_id'] = $getArray['group_id'];
            $orderWhere .= empty($orderWhere) ? 'u.group_id='.$getArray['group_id'] : ' and u.group_id='.$getArray['group_id'];
        }

        $page = $this->params('page',1);
        $array['order_goods_list'] = $this->getDbshopTable('OrderGoodsTable')->pageListOrderGoods(array('page'=>$page, 'page_num'=>20), array($orderWhere.' and dbshop_order.order_state>0 and dbshop_order.refund_state<>1'));
        $array['page']      = $page;

        //客户的等级
        $array['group_array'] = $this->getDbshopTable('UserGroupTable')->listUserGroup();

        return $array;
    }
    /**
     * 销售排行
     * @return array
     */
    public function saleOrderAction()
    {
        $array = array();

        $array['page_type'] = 'saleOrder';//页面类型，用于标识页面左侧的菜单

        //时间判定
        $dateNum = (int) $this->request->getQuery('dateNum');
        if($dateNum <= 0) $dateNum = 7 - 1;
        else $dateNum = $dateNum - 1;//因为是从0开始算，所以减去1
        $dateTime = strtotime("-".$dateNum." day");
        $dateTime = strtotime(date('Y-m-d 00:00:00', $dateTime));

        $array['date_num'] = $dateNum;
        $orderWhere= 'dbshop_order.order_time>'.$dateTime;

        //当GET传过时间值时
        $getArray['start_time'] = $this->request->getQuery('start_time');
        $getArray['end_time']   = $this->request->getQuery('end_time');
        if(!empty($getArray['start_time']) and !empty($getArray['end_time'])) {
            $startTime  = strtotime($getArray['start_time']);
            $endTime    = strtotime($getArray['end_time'].' 24:00:00');
            $dateNum    = round(($endTime - $startTime)/(60*60*24))-1;
            $array['date_num']  = '';
            $array['start_time']= $getArray['start_time'];
            $array['end_time']  = $getArray['end_time'];
            $orderWhere= 'dbshop_order.order_time>='.$startTime.' and dbshop_order.order_time<='.$endTime;

        }

        //当GET传递客户组时
        $getArray['group_id'] = (int) $this->request->getQuery('group_id');
        if($getArray['group_id'] > 0) {
            $array['group_id'] = $getArray['group_id'];
            $orderWhere .= empty($orderWhere) ? 'u.group_id='.$getArray['group_id'] : ' and u.group_id='.$getArray['group_id'];
        }

        $page       = $this->params('page',1);
        $pageNum    = 20;
        $array['order_goods_list'] = $this->getDbshopTable('OrderGoodsTable')->statsOrderGoods(array('page'=>$page, 'page_num'=>$pageNum), array($orderWhere.' and dbshop_order.order_state>0 and dbshop_order.refund_state<>1'));
        $array['page']      = $page;
        $array['page_base_num'] = $pageNum * ($page - 1);

        //客户的等级
        $array['group_array'] = $this->getDbshopTable('UserGroupTable')->listUserGroup();

        return $array;
    }
    /**
     * 分类销售分析
     * @return array
     */
    public function classSaleStatsAction()
    {
        $array = array();
        $array['page_type'] = 'classSaleStats';//页面类型，用于标识页面左侧的菜单

        //统计图表输出
        $dateNum = (int) $this->request->getQuery('dateNum');
        if($dateNum <= 0) $dateNum = 7 - 1;
        else $dateNum = $dateNum - 1;//因为是从0开始算，所以减去1
        $dateTime = strtotime("-".$dateNum." day");
        $dateTime = strtotime(date('Y-m-d 00:00:00', $dateTime));

        $array['date_num'] = $dateNum;
        $orderWhere= 'order_time>'.$dateTime;

        //当GET传过时间值时
        if($this->request->isGet()) {
            $postArray = $this->request->getQuery()->toArray();
            if(!empty($postArray['start_time']) and !empty($postArray['end_time'])) {
                $startTime  = strtotime($postArray['start_time']);
                $endTime    = strtotime($postArray['end_time'].' 24:00:00');
                $dateNum    = round(($endTime - $startTime)/(60*60*24))-1;
                $array['date_num']  = '';
                $array['start_time']= $postArray['start_time'];
                $array['end_time']  = $postArray['end_time'];
                $orderWhere= 'order_time>='.$startTime.' and order_time<='.$endTime;
            }
        }

        //当GET传递客户组时
        $getArray['group_id'] = (int) $this->request->getQuery('group_id');
        if($getArray['group_id'] > 0) {
            $array['group_id'] = $getArray['group_id'];
            $orderWhere .= empty($orderWhere) ? 'u.group_id='.$getArray['group_id'] : ' and u.group_id='.$getArray['group_id'];
        }

        //当GET传递商品分类id时
        $getArray['class_id'] = $this->request->getQuery('class_id');
        $classWhere = '';
        if($getArray['class_id'] > 0) {
            $classInfo = $this->getDbshopTable('GoodsClassTable')->infoGoodsClass(array('class_id'=>$getArray['class_id']));
            if(isset($classInfo->class_path)) {
                $classIdAndSubId = $this->getDbshopTable('GoodsClassTable')->getSunClassId($classInfo->class_id);
                if(is_array($classIdAndSubId) and !empty($classIdAndSubId)) {
                    $classInfo->class_path = implode(',', $classIdAndSubId);
                } else $classInfo->class_path = $classInfo->class_id;
                $classWhere = 'dbshop_order.order_id IN (SELECT distinct og.order_id FROM dbshop_order_goods as og WHERE og.class_id IN ('.$classInfo->class_path.'))';
            }
        }

        $payOrderS = $this->getDbshopTable('OrderTable')->StatsOrder($orderWhere." and ((pay_code<>'xxfk' and pay_code<>'hdfk' and order_state>=20) or order_state=60)", '', 'otime', 'count(order_id) AS order_count', $classWhere);
        $payOrderArray = array();
        if(is_array($payOrderS) and !empty($payOrderS)) {
            foreach($payOrderS as $value) {
                $payOrderArray[$value['otime']] = $value['order_count'];
            }
        }
        $orderS = $this->getDbshopTable('OrderTable')->StatsOrder($orderWhere, '', 'otime', 'count(order_id) AS order_count', $classWhere);
        $orderArray = array();
        if(is_array($orderS) and !empty($orderS)) {
            foreach($orderS as $oValue) {
                $orderArray[$oValue['otime']] = $oValue['order_count'];
            }
        }
        $dateArray = array();
        $pArray    = array();
        $oArray    = array();
        for($i=$dateNum; $i>=0; $i--) {
            $dateStr = (isset($endTime) and !empty($endTime)) ? date("Y-m-d", strtotime("-".$i." day", ($endTime-60*60*24))) : date("Y-m-d", strtotime("-".$i." day"));
            $dateArray[] = '\''.$dateStr.'\'';
            $pArray[$dateStr] = isset($payOrderArray[$dateStr]) ? $payOrderArray[$dateStr] : 0;
            $oArray[$dateStr] = isset($orderArray[$dateStr])    ? $orderArray[$dateStr]     : 0;
        }
        $array['week_pay_order']= implode(',', $pArray);
        $array['week_order']    = implode(',', $oArray);
        $array['date_str']      = implode(',', $dateArray);

        if(!empty($classWhere)) {
            $classWhereExt1 = 'SELECT og.order_goods_id FROM dbshop_order_goods as og WHERE og.class_id IN ('.$classInfo->class_path.') and og.order_id IN (SELECT o.order_id FROM dbshop_order as o WHERE '.$orderWhere.' and order_state>0 and refund_state<>1)';
            $classWhereExt2 = 'SELECT og.order_goods_id FROM dbshop_order_goods as og WHERE og.class_id IN ('.$classInfo->class_path.') and og.order_id IN (SELECT o.order_id FROM dbshop_order as o WHERE '.$orderWhere.' and ((order_state>=0 and order_state<20) or (order_state>=20 and (pay_code=\'xxfk\' or pay_code=\'hdfk\'))) and refund_state<>1)';
            $classWhereExt3 = 'SELECT og.order_goods_id FROM dbshop_order_goods as og WHERE og.class_id IN ('.$classInfo->class_path.') and og.order_id IN (SELECT o.order_id FROM dbshop_order as o WHERE '.$orderWhere.' and ((pay_code<>\'xxfk\' and pay_code<>\'hdfk\' and order_state>=20) or order_state=60) and refund_state<>1)';
        }
        //订单总额
        $array['order_total']       = empty($classWhere) ?
            $this->getDbshopTable('OrderTable')->StatsOrder($orderWhere." and order_state>0 and refund_state<>1", '', '', 'SUM(order_amount) AS order_total')
            : $this->getDbshopTable('OrderGoodsTable')->statsOrderGoodsAmount("dbshop_order_goods.order_goods_id IN (".$classWhereExt1.")");

        //未付款总额
        $array['order_d_pay_total'] = empty($classWhere) ?
            $this->getDbshopTable('OrderTable')->StatsOrder($orderWhere." and ((order_state>=0 and order_state<20) or (order_state>=20 and (pay_code='xxfk' or pay_code='hdfk'))) and refund_state<>1", '', '', 'SUM(order_amount) AS order_total')
            : $this->getDbshopTable('OrderGoodsTable')->statsOrderGoodsAmount("dbshop_order_goods.order_goods_id IN (".$classWhereExt2.")");
        //已付款总额
        $array['order_pay_total']   = empty($classWhere) ?
            $this->getDbshopTable('OrderTable')->StatsOrder($orderWhere." and ((pay_code<>'xxfk' and pay_code<>'hdfk' and order_state>=20) or order_state=60) and refund_state<>1", '', '', 'SUM(order_amount) AS order_total')
            : $this->getDbshopTable('OrderGoodsTable')->statsOrderGoodsAmount("dbshop_order_goods.order_goods_id IN (".$classWhereExt3.")");

        //客户的等级
        $array['group_array'] = $this->getDbshopTable('UserGroupTable')->listUserGroup();

        //商品分类商品销售饼形图
        $goodsClassStr = '';
        $classGoodsArray = array();
        $userGroupClassIdStr = '';//用客户组查询使用
        $classTopId = 0;
        if($getArray['class_id'] > 0) $classTopId = $getArray['class_id'];
        $goodsClass = $this->getDbshopTable('GoodsClassTable')->selectGoodsClass(array('class_id'=>$classTopId));
        $goodsClassArray = $this->getDbshopTable('GoodsClassTable')->selectGoodsClass(array('class_top_id'=>$classTopId));
        if(isset($goodsClass[0]) and !empty($goodsClass[0])) array_unshift($goodsClassArray, $goodsClass[0]);
        if(is_array($goodsClassArray) and !empty($goodsClassArray)) {
            $classNameArray = array();
            foreach($goodsClassArray as $classValue) {
                $classNameArray[] = '\''.$classValue['class_name'] . ($classValue['class_id'] == $classTopId ? ' - '.$this->getDbshopLang()->translate('主分类') : '').'\'';

                //商品分类商品销售
                $classIdAndSubId = $this->getDbshopTable('GoodsClassTable')->getSunClassId($classValue['class_id']);
                if(is_array($classIdAndSubId) and !empty($classIdAndSubId)) {
                    $subClassIdStr = implode(',', $classIdAndSubId);
                } else $subClassIdStr = $classValue['class_id'];

                $userGroupClassIdStr .= empty($userGroupClassIdStr) ? $subClassIdStr : ',' . $subClassIdStr;

                $classWhereExt = 'SELECT og.order_goods_id FROM dbshop_order_goods as og WHERE og.class_id IN ('.$subClassIdStr.') and og.order_id IN (SELECT o.order_id FROM dbshop_order as o WHERE '.$orderWhere.' and order_state>0 and refund_state<>1)';
                $orderGoodsCount = $this->getDbshopTable('OrderGoodsTable')->statsOrderGoodsAmount("dbshop_order_goods.order_goods_id IN (".$classWhereExt.")", 'SUM(dbshop_order_goods.buy_num) AS goods_count');
                $classGoodsArray[] = array(
                    'num' => (!empty($orderGoodsCount[0]['goods_count']) ? $orderGoodsCount[0]['goods_count'] : 0),
                    'name'=> '\''.$classValue['class_name'] . ($classValue['class_id'] == $classTopId ? ' - '.$this->getDbshopLang()->translate('主分类') : '').'\''
                );
            }
            $goodsClassStr = implode(',', $classNameArray);
        }
        $array['goods_class_name_str']  = $goodsClassStr;
        $array['class_goods_array']     = $classGoodsArray;

        //客户组销售
        $userGroupArray = array();
        $userGroupName = array();
        if(!empty($userGroupClassIdStr)) {
            $subClassIdWhere = ' og.class_id IN ('.$userGroupClassIdStr.') and ';
            if($getArray['group_id'] > 0) {
                $userGroup = $this->getDbshopTable('UserGroupExtendTable')->infoUserGroupExtend(array('group_id'=>$getArray['group_id']));
                $groupArray[0] = (array) $userGroup;
            } else $groupArray = $array['group_array'];

            if(is_array($groupArray) and !empty($groupArray)) {
                foreach($groupArray as $groupValue) {
                    $userGroupName[] = '\''.$groupValue['group_name'].'\'';
                    $groupWhereExt = 'SELECT og.order_goods_id FROM dbshop_order_goods as og WHERE '.$subClassIdWhere.' og.order_id IN (SELECT o.order_id FROM dbshop_order as o WHERE '.$orderWhere.' and u.group_id='.$groupValue['group_id'].' and order_state>0 and refund_state<>1)';
                    $userGroupGoodsCount = $this->getDbshopTable('OrderGoodsTable')->statsOrderGoodsAmount("dbshop_order_goods.order_goods_id IN (".$groupWhereExt.")", 'SUM(dbshop_order_goods.buy_num) AS goods_count');
                    $userGroupArray[] = array(
                        'num' => (!empty($userGroupGoodsCount[0]['goods_count']) ? $userGroupGoodsCount[0]['goods_count'] : 0),
                        'name'=> '\''.$groupValue['group_name'].'\''
                    );
                }
            }
        }
        $array['user_group_name_str']   = !empty($userGroupName) ? implode(',', $userGroupName) : '';
        $array['user_group_goods_array']= $userGroupArray;

        /*---------------------------------------------------------------------*/
        //时间判定
        $dateNum = (int) $this->request->getQuery('dateNum');
        if($dateNum <= 0) $dateNum = 7 - 1;
        else $dateNum = $dateNum - 1;//因为是从0开始算，所以减去1
        $dateTime = strtotime("-".$dateNum." day");
        $dateTime = strtotime(date('Y-m-d 00:00:00', $dateTime));

        $array['date_num'] = $dateNum;
        $orderWhere= 'dbshop_order.order_time>'.$dateTime;

        //当GET传过时间值时
        $getArray['start_time'] = $this->request->getQuery('start_time');
        $getArray['end_time']   = $this->request->getQuery('end_time');
        if(!empty($getArray['start_time']) and !empty($getArray['end_time'])) {
            $startTime  = strtotime($getArray['start_time']);
            $endTime    = strtotime($getArray['end_time'].' 24:00:00');
            $dateNum    = round(($endTime - $startTime)/(60*60*24))-1;
            $array['date_num']  = '';
            $array['start_time']= $getArray['start_time'];
            $array['end_time']  = $getArray['end_time'];
            $orderWhere= 'dbshop_order.order_time>='.$startTime.' and dbshop_order.order_time<='.$endTime;

        }
        //当GET传递客户组时
        $getArray['group_id'] = (int) $this->request->getQuery('group_id');
        if($getArray['group_id'] > 0) {
            $array['group_id'] = $getArray['group_id'];
            $orderWhere .= empty($orderWhere) ? 'u.group_id='.$getArray['group_id'] : ' and u.group_id='.$getArray['group_id'];
        }

        //当GET传递商品分类id时
        if(isset($classInfo->class_path)) {
            $array['class_id'] = $getArray['class_id'];
            $orderWhere .= empty($orderWhere) ? 'dbshop_order_goods.class_id IN('.$classInfo->class_path.')' : ' and dbshop_order_goods.class_id IN('.$classInfo->class_path.')';
        }

        $page = $this->params('page',1);
        $pageNum = 20;
        $array['order_goods_list'] = $this->getDbshopTable('OrderGoodsTable')->statsOrderGoods(array('page'=>$page, 'page_num'=>$pageNum), array($orderWhere.' and dbshop_order.order_state>0 and dbshop_order.refund_state<>1'));
        $array['page']      = $page;
        $array['page_base_num'] = $pageNum * ($page - 1);

        //商品分类
        $classArray = $this->getDbshopTable('GoodsClassTable')->listGoodsClass();
        $array['class_array'] = $this->getDbshopTable('GoodsClassTable')->classOptions(0,$classArray);

        return $array;
    }
    /**
     * 流量概况
     * @return array
     */
    public function indexAction()
    {
        $array = array();

        return $array;
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
}