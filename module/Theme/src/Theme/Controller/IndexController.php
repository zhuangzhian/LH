<?php
/**
 * DBShop 电子商务系统
 *
 * ==========================================================================
 * @link      http://www.dbshop.net/
 * @copyright Copyright (c) 2012-2018 DBShop.net Inc. (http://www.dbshop.net)
 * @license   http://www.dbshop.net/license.html License
 * ==========================================================================
 *
 * @author    静静的风
 *
 */

namespace Theme\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    private $dbTables = array();
    private $translator;

    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTerminal(true);

        $themeTitle = $this->params('title');

        $themeInfo = $this->getDbshopTable('ThemeTable')->infoTheme(array('theme_sign'=>$themeTitle));
        if(empty($themeTitle) or empty($themeInfo)) return $this->redirect()->toRoute('shopfront/default');
        if($themeInfo->theme_state == 2) exit($this->getDbshopLang()->translate('该专题不存在！'));
        if(!file_exists(DBSHOP_PATH . '/module/Theme/view/theme/'.$themeInfo->theme_template.'/'.$themeInfo->theme_template.'.phtml')) return $this->redirect()->toRoute('shopfront/default');

        $view->setTemplate('/theme/'.$themeInfo->theme_template.'/'.$themeInfo->theme_template.'.phtml');

        $array = array();

        $array['theme_info'] = $themeInfo;

        $itemArray = $this->getDbshopTable('ThemeItemTable')->listItem(array('theme_template'=>$themeInfo->theme_template, 'theme_id'=>$themeInfo->theme_id));
        if(!empty($itemArray)) {
            foreach ($itemArray as $itemValue) {
                if($itemValue['item_type'] == 'goods_set') {//商品信息
                    $goodsArray = $this->getDbshopTable('ThemeGoodsTable')->frontShowGoods(array('dbshop_theme_goods.item_id'=>$itemValue['item_id']));
                    if(!empty($goodsArray)) $array['goods'][$itemValue['item_code']] = $goodsArray;
                    $array['goods_type_title'][$itemValue['item_code']] = $itemValue['item_title'];
                }
                if($itemValue['item_type'] == 'ad_set') {//广告信息
                    $adInfo = $this->getDbshopTable('ThemeAdTable')->infoAd(array('item_id'=>$itemValue['item_id']));
                    if(!empty($adInfo)) {
                        if($adInfo['theme_ad_type'] == 'slide') {
                            $slideArray = $this->getDbshopTable('ThemeAdSlideTable')->listAdSlide(array('item_id'=>$itemValue['item_id']));
                            if(!empty($slideArray)) $array['ad'][$itemValue['item_code']] = $slideArray;
                        } else {
                            $array['ad'][$itemValue['item_code']] = $adInfo;
                        }
                    }
                }
                if($itemValue['item_type'] == 'cms_set') {//文章信息

                }
            }
        }

        return $view->setVariables($array);
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