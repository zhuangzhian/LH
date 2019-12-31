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

namespace Admin\Controller;

class HomeController extends BaseController
{
    /**
     * The default action - show the home page
     */
    public function indexAction ()
    {
        $array = array();
        //订单信息
        $array['order_no_payment'] = $this->getDbshopTable('OrderTable')->stateCountOrder(array('order_state >= 10 and order_state <= 15'));//待付款
        $array['order_yes_payment']= $this->getDbshopTable('OrderTable')->stateCountOrder(array('order_state'=>20));//已付款
        $array['order_no_send']    = $this->getDbshopTable('OrderTable')->stateCountOrder(array('order_state>=20 and order_state<40'));//待发货
        $array['order_no_finish']  = $this->getDbshopTable('OrderTable')->stateCountOrder(array('order_state'=>40));//已发货
        $array['order_finish']     = $this->getDbshopTable('OrderTable')->stateCountOrder(array('order_state'=>60));//已完成
        $array['order_cancel']     = $this->getDbshopTable('OrderTable')->stateCountOrder(array('order_state'=>0));//已取消
        $array['refund_n']     = $this->getDbshopTable('OrderRefundTable')->countOrderRefund(array('refund_state'=>0));//未处理的退货
        $array['refund_y']     = $this->getDbshopTable('OrderRefundTable')->countOrderRefund(array('refund_state!=0'));//已处理的退货

        //商品信息
        $array['open_goods']       = $this->getDbshopTable('GoodsTable')->countGoods(array('goods_state=1'));
        $array['close_goods']      = $this->getDbshopTable('GoodsTable')->countGoods(array('goods_state=2'));
        $array['preferential_goods']= $this->getDbshopTable('GoodsTable')->countGoods(array('goods_preferential_price>0'));
        $array['sell_goods']       = $this->getDbshopTable('OrderGoodsTable')->countOrderGoods();
        $array['goods_class_num']  = $this->getDbshopTable('GoodsClassTable')->countGoodsClass(array('1=1'));
        $array['out_stock_goods']  = $this->getDbshopTable('GoodsTable')->countGoods(array('goods_out_of_stock_set >= goods_stock'));

        //其他信息
        $array['comment_count']    = $this->getDbshopTable('GoodsCommentTable')->goodsCommentCount();
        $array['ask_count']        = $this->getDbshopTable('GoodsAskTable')->countGoodsAsk();

        //会员信息
        $array['user_count']       = $this->getDbshopTable('UserTable')->countUser();
        $array['auth_count']       = $this->getDbshopTable('UserTable')->countUser(array('user_state=3'));
        $array['close_count']      = $this->getDbshopTable('UserTable')->countUser(array('user_state=2'));
        $array['buy_user_count']   = $this->getDbshopTable('OrderGoodsTable')->buyGoodsUserCount();

        //系统信息
        include DBSHOP_PATH . '/data/Version.php';

        $array['dbshop_version']        = DBSHOP_VERSION;
        $array['dbshop_charset']        = DBSHOP_CHARSET;
        $array['dbshop_install_time']   = DBSHOP_INSTALL_TIME;
        $array['dbshop_update_time']    = DBSHOP_UPDATE_TIME;
      
        return $array;
    }

    public function ajaxServiceMessageAction() {
        //系统信息
        include DBSHOP_PATH . '/data/Version.php';
        //soap获取远程版本信息
        $array['dbshop_update_message'] = '';
        //获取远程模板更新信息
        $array['template_update_message'] = '';
        //获取远程插件更新信息
        $array['plugin_update_message'] = '';

        if(class_exists('SoapClient')) {
            try {
                $soapClient = new \SoapClient(null, array(
                    //'location' => 'https://update.dbshop.net/packageservice',//用https方式，必须用户本地开启open_ssl，增加了繁琐性，所以不启用了
                    'location' => 'http://update.dbshop.net/packageservice',
                    'uri'      => 'dbshop_package_update'
                ));
                //获取插件更新提示信息
                $pluginList = $this->getDbshopTable('PluginTable')->listPlugin();
                $pluginWhere= '';
                $pluginArray= array();
                if(is_array($pluginList) and !empty($pluginList)) {
                    foreach($pluginList as $pValue) {
                        $pluginArray[$pValue['plugin_code']] = $pValue['plugin_version_num'];
                        $pluginWhere .= "plugin_code='".$pValue['plugin_code']."' or ";
                    }
                }
                if(!empty($pluginWhere)) {
                    $pluginWhere = substr($pluginWhere, 0, -4);
                }

                //获取模板更新提示信息
                $templateIniReader = new \Zend\Config\Reader\Ini();
                $templatePath      = DBSHOP_PATH . '/module/Shopfront/view/';
                $where             = '';
                $templateArray     = array();
                if(is_dir($templatePath)) {
                    $dh = opendir($templatePath);
                    while (false !== ($dirName = readdir($dh))) {
                        if($dirName != '.' and $dirName != '..' and $dirName != 'default' and $dirName != '.DS_Store') {
                            $templateIni = $templateIniReader->fromFile($templatePath . $dirName . '/shopfront/template.ini');
                            $templateArray[$dirName] = (isset($templateIni['template_info']['version_number']) ? $templateIni['template_info']['version_number'] : 0);
                            //组合where语句，用于webservice
                            $where .= "shop_template.template_str='".$dirName."' or ";
                        }
                    }
                }
                if(!empty($where)) {
                    $where = '('.substr($where, 0, -4).')';
                }

                $phoneTemplatePath  = DBSHOP_PATH . '/module/Mobile/view/';
                $phoneWhere         = '';
                $phoneTemplateArray = array();
                if(is_dir($phoneTemplatePath)) {
                    $pdh = opendir($phoneTemplatePath);
                    while (false !== ($pDirName = readdir($pdh))) {
                        if($pDirName != '.' and $pDirName != '..' and $pDirName != 'default' and $pDirName != '.DS_Store') {
                            $pTemplateIni = $templateIniReader->fromFile($phoneTemplatePath . $pDirName . '/mobile/template.ini');
                            $phoneTemplateArray[$pDirName] = (isset($pTemplateIni['template_info']['version_number']) ? $pTemplateIni['template_info']['version_number'] : 0);
                            //组合where语句，用于webservice
                            $phoneWhere .= "shop_phone_template.template_str='".$pDirName."' or ";
                        }
                    }
                }
                if(!empty($phoneWhere)) {
                    $phoneWhere = '('.substr($phoneWhere, 0, -4).')';
                }

                //获取是否有新的更新包、模板信息，新闻信息、授权信息
                $dbshopArray = $soapClient->dbshopPackageAndNewsAndAuthorization(
                    array(
                        'dbshop_version_number' => DBSHOP_VERSION_NUMBER,
                        'dbshop_version'        => DBSHOP_VERSION,
                        'phone_template_where'  => empty($phoneWhere)   ? array() : array($phoneWhere .' and v.support_version<='.DBSHOP_VERSION_NUMBER),
                        'template_where'        => empty($where)        ? array() : array($where .' and v.support_version<='.DBSHOP_VERSION_NUMBER),
                        'plugin_where'          => empty($pluginWhere)  ? array() : array($pluginWhere),
                        'authorization'         => $this->getServiceLocator()->get('frontHelper')->dbshopHttpHost()
                    )
                );
                //模板更新信息
                $onlineTemplate = isset($dbshopArray['templateInfo']) ? $dbshopArray['templateInfo'] : array();
                if(!empty($onlineTemplate) and is_array($onlineTemplate)) {
                    foreach($onlineTemplate as $templateValue) {
                        if(isset($templateArray[$templateValue['template_str']]) and $templateValue['template_version_number'] > $templateArray[$templateValue['template_str']]) {
                            $array['template_update_message'] = '<div class="alert alert-error" style="margin-bottom:8px;"><h4><i class="cus-bell"></i> '.$this->getDbshopLang()->translate('您的PC模板有新更新包啦！').'<a href="'.$this->url()->fromRoute('template/default').'" class="btn btn-small btn-primary"><i class="icon-circle-arrow-right icon-white"></i> '.$this->getDbshopLang()->translate('马上去查看一下').'</a></h4></div>';
                            break;
                        }
                    }
                }
                $onlinePhoneTemplate = isset($dbshopArray['phoneTemplateInfo']) ? $dbshopArray['phoneTemplateInfo'] : array();
                if(!empty($onlinePhoneTemplate) and is_array($onlinePhoneTemplate)) {
                    foreach ($onlinePhoneTemplate as $pTemplateValue) {
                        if(isset($phoneTemplateArray[$pTemplateValue['template_str']]) and $pTemplateValue['template_version_number'] > $phoneTemplateArray[$pTemplateValue['template_str']]) {
                            $array['phone_template_update_message'] = '<div class="alert alert-error" style="margin-bottom:8px;"><h4><i class="cus-bell"></i> '.$this->getDbshopLang()->translate('您的手机模板有新更新包啦！').'<a href="'.$this->url()->fromRoute('phonetemplate/default').'" class="btn btn-small btn-primary"><i class="icon-circle-arrow-right icon-white"></i> '.$this->getDbshopLang()->translate('马上去查看一下').'</a></h4></div>';
                            break;
                        }
                    }
                }
                //插件更新信息
                $onlinePlugin = isset($dbshopArray['pluginInfo']) ? $dbshopArray['pluginInfo'] : array();
                if(!empty($onlinePlugin) and is_array($onlinePlugin)) {
                    foreach($onlinePlugin as $pluginValue) {
                        if(isset($pluginArray[$pluginValue['plugin_code']]) and $pluginValue['plugin_version_num'] > $pluginArray[$pluginValue['plugin_code']]) {
                            $array['plugin_update_message'] = '<div class="alert alert-error" style="margin-bottom:8px;"><h4><i class="cus-bell"></i> '.$this->getDbshopLang()->translate('您的插件有新更新包啦！').'<a href="'.$this->url()->fromRoute('plugin/default').'" class="btn btn-small btn-primary"><i class="icon-circle-arrow-right icon-white"></i> '.$this->getDbshopLang()->translate('马上去查看一下').'</a></h4></div>';
                            break;
                        }
                    }
                }
                //版本更新提醒信息
                if(isset($dbshopArray['updatePackage']) and !empty($dbshopArray['updatePackage'])) $array['dbshop_update_message'] = '<div class="alert alert-error" style="margin-bottom:8px;"><h4><i class="cus-bell"></i> '.$this->getDbshopLang()->translate('DBShop电子商务系统有新更新包啦！').'<a href="'.$this->url()->fromRoute('package/default',array('action'=>'index')).'" class="btn btn-small btn-primary"><i class="icon-circle-arrow-right icon-white"></i> '.$this->getDbshopLang()->translate('马上去查看一下').'</a></h4></div>';
                //新闻动态提醒信息
                $array['dbshop_message'] = isset($dbshopArray['messageList']) ? $dbshopArray['messageList'] : array();
                $messageHtml = '';
                if(is_array($array['dbshop_message']) and !empty($array['dbshop_message'])) {
                    $messageHtml .= '<table class="table table-bordered table-condensed"><thead class="admin_add_header_well"><th>'.$this->getDbshopLang()->translate('DBShop新闻动态').'</th></thead>';
                    foreach($array['dbshop_message'] as $messageValue) {
                        $messageHtml .= '<tr><td>';
                        $messageHtml .= '<a href="'.$messageValue['message_url'].'" target="_blank"><i class="icon-volume-down"></i>'.$messageValue['message_title'].'</a>&nbsp;&nbsp;'.date("Y-m-d H:i", $messageValue['message_add_time']);
                        $messageHtml .= '</td></tr>';
                    }
                    $messageHtml .= '</table>';
                }
                //版权信息
                $authorizationHtml = '';
                if($dbshopArray['authorization']) {
                    $authorizationHtml = '<strong>'.$this->getDbshopLang()->translate('商业授权版本').'</strong>';
                } else {
                    $authorizationHtml = '<font color="red">'.$this->getDbshopLang()->translate('免费(非授权)版本').'</font>&nbsp;&nbsp;<a href="http://shop.dbshop.net/" class="btn btn-small btn-primary" target="_blank"><i class="icon-circle-arrow-right icon-white"></i>'.$this->getDbshopLang()->translate('获取授权').'</a>';
                }

            } catch (\Exception $e) {
                $array['dbshop_update_message'] = '<div class="alert alert-error" style="margin-bottom:8px;"><h4>'.$this->getDbshopLang()->translate('更新服务器连接失败无法获取更新信息！').'</h4></div>';

                $messageHtml = '<table class="table table-bordered"><thead class="admin_add_header_well"><th>'.$this->getDbshopLang()->translate('DBShop新闻动态').'</th></thead>';
                $messageHtml .= '<tr><td><strong>'.$this->getDbshopLang()->translate('连接失败无法获取DBShop新闻！').'</strong></td></tr>';
                $messageHtml .= '</table>';
            }
        } else {
            $array['dbshop_update_message'] = '<div class="alert alert-error" style="margin-bottom:8px;"><h4>'.$this->getDbshopLang()->translate('您的环境没有开启soap，更新服务器连接失败无法获取更新信息！').'</h4></div>';

            $messageHtml = '<table class="table table-bordered"><thead class="admin_add_header_well"><th>'.$this->getDbshopLang()->translate('DBShop新闻动态').'</th></thead>';
            $messageHtml .= '<tr><td><strong>'.$this->getDbshopLang()->translate('您的环境没有开启soap，无法获取DBShop新闻！').'</strong></td></tr>';
            $messageHtml .= '</table>';
        }

        $jsonArray = array();
        $jsonArray['template_update_message'] = $array['dbshop_update_message'].$array['template_update_message'].$array['phone_template_update_message'].$array['plugin_update_message'];
        $jsonArray['news_html']               = $messageHtml;
        $jsonArray['auth_html']               = $authorizationHtml;
        echo json_encode($jsonArray);
        exit;
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