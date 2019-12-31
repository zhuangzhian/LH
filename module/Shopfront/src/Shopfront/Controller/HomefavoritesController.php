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

namespace Shopfront\Controller;

use Shopfront\Controller\FronthomeController;
use Zend\View\Model\ViewModel;

class HomefavoritesController extends FronthomeController
{
    private $dbTables = array();
    private $translator;
    
    /**
     * The default action - show the home page
     */
    public function indexAction ()
    {
        $view = new ViewModel();
        $view->setTemplate('/shopfront/home/goodsfavorites.phtml');
        
        //顶部title使用
        $this->layout()->title_name = $this->getDbshopLang()->translate('我的收藏');
        
        $array = array();
        
        $page 		  = $this->params('page',1);
        $array['page']= $page;
        $array['favorites_list'] = $this->getDbshopTable('UserFavoritesTable')->listFavorites(array('page'=>$page, 'page_num'=>16), array('dbshop_user_favorites.user_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')));

        $view->setVariables($array);
        return $view;
    }
    /**
     * 商品收藏删除
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function delAction ()
    {
        $favoritesId = (int) $this->params('favorites_id', 0);
        if($favoritesId > 0) {
            $this->getDbshopTable('UserFavoritesTable')->delFavorites(array('favorites_id'=>$favoritesId, 'user_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')));
        }
        return $this->redirect()->toRoute('frontfavorites/default');
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
