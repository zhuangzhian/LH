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


use Admin\Controller\BaseController;
use Zend\Config\Reader\Ini;
use Zend\View\Model\ViewModel;

class ThemeController extends BaseController
{
    /**
     * 专题列表
     * @return array
     */
    public function indexAction()
    {
        $array = array();
        $iniReader = new Ini();

        $array['theme_list'] = $this->getDbshopTable('ThemeTable')->listTheme();
        if(!empty($array['theme_list'])) {
            foreach ($array['theme_list'] as $key => $value) {
                if(file_exists(DBSHOP_PATH . '/module/Theme/view/theme/' . $value['theme_template'] . '/' . $value['theme_template'] .'.ini')) {
                    $value['theme_config'] = $iniReader->fromFile(DBSHOP_PATH . '/module/Theme/view/theme/' . $value['theme_template'] . '/' . $value['theme_template'] .'.ini');
                }
                $array['theme_list'][$key] = $value;
            }
        }

        return $array;
    }
    /**
     * 专题添加
     * @return array
     */
    public function addAction()
    {
        $array = array();

        $iniReader = new Ini();
        $themeTemplateArray = array();
        $filePath  = DBSHOP_PATH . '/module/Theme/view/theme/';

        if($this->request->isPost()) {
            $postArray = $this->request->getPost()->toArray();

            if(file_exists($filePath . $postArray['theme_template'] . '/' . $postArray['theme_template'] . '.ini')) {
                $themeId = $this->getDbshopTable('ThemeTable')->addTheme($postArray);

                $themeConfig = $iniReader->fromFile($filePath . $postArray['theme_template'] . '/' . $postArray['theme_template'] . '.ini');

                if(isset($themeConfig['goods_set']) and !empty($themeConfig['goods_set'])) {
                    foreach ($themeConfig['goods_set'] as $goodsValue) {
                        $this->getDbshopTable('ThemeItemTable')->addItem(
                            array(
                                'item_title'=> $goodsValue['name'],
                                'item_type' => 'goods_set',
                                'item_code' => $goodsValue['code'],
                                'theme_template' => $postArray['theme_template'],
                                'theme_id'  => $themeId
                            )
                        );
                    }
                }
                if(isset($themeConfig['ad_set']) and !empty($themeConfig['ad_set'])) {
                    foreach ($themeConfig['ad_set'] as $adValue) {
                        $this->getDbshopTable('ThemeItemTable')->addItem(
                            array(
                                'item_title'=> $adValue['name'],
                                'item_type' => 'ad_set',
                                'item_code' => $adValue['code'],
                                'theme_template' => $postArray['theme_template'],
                                'theme_id'  => $themeId
                            )
                        );
                    }
                }
                if(isset($themeConfig['cms_set']) and !empty($themeConfig['cms_set'])) {
                    foreach ($themeConfig['cms_set'] as $cmsValue) {
                        $this->getDbshopTable('ThemeItemTable')->addItem(
                            array(
                                'item_title'=> $cmsValue['name'],
                                'item_type' => 'cms_set',
                                'item_code' => $cmsValue['code'],
                                'theme_template' => $postArray['theme_template'],
                                'theme_id'  => $themeId
                            )
                        );
                    }
                }
            }
            $this->redirect()->toRoute('admintheme/default');
        }

        if(is_dir($filePath)) {
            $dh = opendir($filePath);
            while (false !== ($templatePath = readdir($dh))) {
                if($templatePath != '.' and $templatePath != '..' and $templatePath != '.DS_Store') {
                    if(file_exists($filePath . $templatePath . '/' . $templatePath . '.ini')) $themeTemplateArray[$templatePath] = $iniReader->fromFile($filePath . $templatePath . '/' . $templatePath . '.ini');
                }
            }
        }

        $array['theme_template'] = $themeTemplateArray;

        return $array;
    }
    /**
     * 编辑专题
     * @return array
     */
    public function editAction()
    {
        $themeId = (int) $this->request->getQuery('theme_id');
        $themeInfo = $this->getDbshopTable('ThemeTable')->infoTheme(array('theme_id'=>$themeId));
        if(empty($themeInfo)) $this->redirect()->toRoute('admintheme/default');

        $iniReader = new Ini();
        $themeTemplateArray = array();
        $filePath  = DBSHOP_PATH . '/module/Theme/view/theme/';

        if($this->request->isPost()) {
            $postArray = $this->request->getPost()->toArray();

            if(file_exists($filePath . $postArray['theme_template'] . '/' . $postArray['theme_template'] . '.ini')) {
                $themeConfig = $iniReader->fromFile($filePath . $postArray['theme_template'] . '/' . $postArray['theme_template'] . '.ini');

                $this->getDbshopTable('ThemeTable')->updateTheme(
                    array(
                        'theme_name'        => trim($postArray['theme_name']),
                        'theme_template'    => trim($postArray['theme_template']),
                        'theme_state'       => intval($postArray['theme_state']),
                        'theme_extend_name' => trim($postArray['theme_extend_name']),
                        'theme_keywords'    => trim($postArray['theme_keywords']),
                        'theme_description' => trim($postArray['theme_description'])
                    ),
                    array(
                        'theme_id' => $themeId
                    )
                );

                if(isset($themeConfig['goods_set']) and !empty($themeConfig['goods_set'])) {
                    foreach ($themeConfig['goods_set'] as $goodsValue) {
                        $goodsItem = $this->getDbshopTable('ThemeItemTable')->infoItem(array('item_type'=>'goods_set', 'item_code'=>$goodsValue['code'], 'theme_template'=>$postArray['theme_template']));
                        if(empty($goodsItem)) {
                            $this->getDbshopTable('ThemeItemTable')->addItem(
                                array(
                                    'item_title'=> $goodsValue['name'],
                                    'item_type' => 'goods_set',
                                    'item_code' => $goodsValue['code'],
                                    'theme_template' => $postArray['theme_template'],
                                    'theme_id'  => $themeId
                                )
                            );
                        }
                    }
                }
                if(isset($themeConfig['ad_set']) and !empty($themeConfig['ad_set'])) {
                    foreach ($themeConfig['ad_set'] as $adValue) {
                        $adItem = $this->getDbshopTable('ThemeItemTable')->infoItem(array('item_type'=>'ad_set', 'item_code'=>$adValue['code'], 'theme_template'=>$postArray['theme_template']));
                        if(empty($adItem)) {
                            $this->getDbshopTable('ThemeItemTable')->addItem(
                                array(
                                    'item_title'=> $adValue['name'],
                                    'item_type' => 'ad_set',
                                    'item_code' => $adValue['code'],
                                    'theme_template' => $postArray['theme_template'],
                                    'theme_id'  => $themeId
                                )
                            );
                        }
                    }
                }
                if(isset($themeConfig['cms_set']) and !empty($themeConfig['cms_set'])) {
                    foreach ($themeConfig['cms_set'] as $cmsValue) {
                        $cmsItem = $this->getDbshopTable('ThemeItemTable')->infoItem(array('item_type'=>'cms_set', 'item_code'=>$cmsValue['code'], 'theme_template'=>$postArray['theme_template']));
                        if(empty($cmsItem)) {
                            $this->getDbshopTable('ThemeItemTable')->addItem(
                                array(
                                    'item_title'=> $cmsValue['name'],
                                    'item_type' => 'cms_set',
                                    'item_code' => $cmsValue['code'],
                                    'theme_template' => $postArray['theme_template'],
                                    'theme_id'  => $themeId
                                )
                            );
                        }
                    }
                }
            }
            $this->redirect()->toRoute('admintheme/default');
        }

        $array = array();

        $array['theme_info'] = $themeInfo;

        if(is_dir($filePath)) {
            $dh = opendir($filePath);
            while (false !== ($templatePath = readdir($dh))) {
                if($templatePath != '.' and $templatePath != '..' and $templatePath != '.DS_Store') {
                    if(file_exists($filePath . $templatePath . '/' . $templatePath . '.ini')) $themeTemplateArray[$templatePath] = $iniReader->fromFile($filePath . $templatePath . '/' . $templatePath . '.ini');
                }
            }
        }

        $array['theme_template'] = $themeTemplateArray;

        return $array;
    }
    /**
     * 删除专题
     */
    public function delAction()
    {
        $themeId = (int) $this->request->getPost('theme_id');
        if($themeId > 0) {
            $this->getDbshopTable('ThemeTable')->delTheme(array('theme_id'=>$themeId));
            $itemArray = $this->getDbshopTable('ThemeItemTable')->listItem(array('theme_id'=>$themeId));
            if(!empty($itemArray)) {
                foreach ($itemArray as $value) {
                    $this->getDbshopTable('ThemeGoodsTable')->delGoods(array('item_id'=>$value['item_id']));
                    $this->getDbshopTable('ThemeCmsTable')->delCms(array('item_id'=>$value['item_id']));
                    $this->getDbshopTable('ThemeAdTable')->delAd(array('item_id'=>$value['item_id']));
                    $this->getDbshopTable('ThemeAdSlideTable')->delAdSlide(array('item_id'=>$value['item_id']));
                }
                $this->getDbshopTable('ThemeItemTable')->delItem(array('theme_id'=>$themeId));
            }

            exit('true');
        }
        exit('false');
    }
    /**
     * 检查专题标记是否重复
     */
    public function checkAction()
    {
        $themeSign = trim($this->request->getPost('theme_sign'));
        $themeInfo = $this->getDbshopTable('ThemeTable')->infoTheme(array('theme_sign'=>$themeSign));
        if($themeInfo) exit('false');
        else exit('true');

    }
    /**
     * 商品设置
     * @return array
     */
    public function goodsListAction()
    {
        $themeId    = (int) $this->request->getQuery('theme_id');

        $themeInfo = $this->getDbshopTable('ThemeTable')->infoTheme(array('theme_id'=>$themeId));
        if(empty($themeInfo)) $this->redirect()->toRoute('admintheme/default');

        $array = array();

        $array['goods_set_list'] = $this->getDbshopTable('ThemeItemTable')->listGoodsItem(array('dbshop_theme_item.theme_id'=>$themeId, 'dbshop_theme_item.item_type'=>'goods_set', 'dbshop_theme_item.theme_template'=>$themeInfo->theme_template));

        return $array;
    }
    /**
     * 商品管理
     * @return array
     */
    public function goodsSetAction()
    {
        $themeId    = (int) $this->request->getQuery('theme_id');
        $itemId    = (int) $this->request->getQuery('item_id');

        $array = array();

        if($this->request->isPost()) {
            $postArray = $this->request->getPost()->toArray();

            $this->getDbshopTable('ThemeItemTable')->updateItem(array('item_title'=>trim($postArray['item_title'])), array('theme_id'=>$postArray['theme_id'], 'item_id'=>$postArray['item_id']));
            if(is_array($postArray['theme_goods_id']) and !empty($postArray['theme_goods_id'])) {
                foreach ($postArray['theme_goods_id'] as $key => $value) {
                    $this->getDbshopTable('ThemeGoodsTable')->updateGoods(array('goods_sort'=>$value), array('theme_goods_id'=>$key, 'item_id'=>$postArray['item_id']));
                }
            }
            $array['success_msg'] = $this->getDbshopLang()->translate('信息更新成功！');
        }

        $itemInfo = $this->getDbshopTable('ThemeItemTable')->infoItem(array('theme_id'=>$themeId, 'item_id'=>$itemId));
        if(empty($itemInfo)) $this->redirect()->toRoute('admintheme/default');
        $array['item_info'] = $itemInfo;

        return $array;
    }
    /**
     * 添加商品信息
     */
    public function addGoodsAction()
    {
        $itemId     = intval($this->request->getPost('item_id'));
        $goodsId    = intval($this->request->getPost('goods_id'));

        if($itemId > 0 and $goodsId > 0) {
            $GoodsInfo  = $this->getDbshopTable('ThemeGoodsTable')->infoGoods(array('item_id'=>$itemId, 'goods_id'=>$goodsId));
            if(!empty($GoodsInfo)) exit(json_encode(array('state'=>'have')));

            $GoodsArray = array();
            $GoodsArray['item_id']      = $itemId;
            $GoodsArray['goods_id']     = $goodsId;
            $GoodsArray['goods_sort']  = 255;
            $this->getDbshopTable('ThemeGoodsTable')->addGoods($GoodsArray);

            exit(json_encode(array('state'=>'true')));
        }
        exit(json_encode(array('state'=>'false')));
    }
    /**
     * 删除商品
     */
    public function delGoodsAction()
    {
        $itemId     = intval($this->request->getPost('item_id'));
        $themeGoodsId= intval($this->request->getPost('theme_goods_id'));
        if($this->getDbshopTable('ThemeGoodsTable')->delGoods(array('item_id'=>$itemId, 'theme_goods_id'=>$themeGoodsId))) exit('true');
        exit('false');
    }
    /**
     * 项目商品列表
     * @return ViewModel
     */
    public function listGoodsAction()
    {
        $view = new ViewModel();
        $view->setTerminal(true);

        $array = array();
        $itemId = (int)$this->request->getQuery('item_id');

        $array['goods_list'] = $this->getDbshopTable('ThemeGoodsTable')->listGoods(array('dbshop_theme_goods.item_id'=>$itemId));

        return $view->setVariables($array);
    }
    /**
     * 广告设置
     * @return array
     */
    public function adListAction()
    {
        $themeId    = (int) $this->request->getQuery('theme_id');

        $themeInfo = $this->getDbshopTable('ThemeTable')->infoTheme(array('theme_id'=>$themeId));
        if(empty($themeInfo)) $this->redirect()->toRoute('admintheme/default');

        $iniReader = new Ini();
        $themeAdIni= array();
        $filePath  = DBSHOP_PATH . '/module/Theme/view/theme/';

        $array = array();

        $array['theme_info'] = $themeInfo;

        $array['ad_set_list'] = $this->getDbshopTable('ThemeItemTable')->listAdItem(array('dbshop_theme_item.theme_id'=>$themeId, 'dbshop_theme_item.item_type'=>'ad_set', 'dbshop_theme_item.theme_template'=>$themeInfo->theme_template));

        if(file_exists($filePath . $themeInfo->theme_template . '/' . $themeInfo->theme_template . '.ini')) {
            $themeTemplateArray = $iniReader->fromFile($filePath . $themeInfo->theme_template . '/' . $themeInfo->theme_template . '.ini');
            if(isset($themeTemplateArray['ad_set']) and !empty($themeTemplateArray['ad_set'])) {
                foreach ($themeTemplateArray['ad_set'] as $value) {
                    $themeAdIni[$value['code']] = $value;
                }
            }
        }

        $array['ad_ini'] = $themeAdIni;

        return $array;
    }
    /**
     * 广告设置
     * @return array
     */
    public function adSetAction()
    {
        $themeId    = (int) $this->request->getQuery('theme_id');
        $itemId    = (int) $this->request->getQuery('item_id');

        $array = array();

        if($this->request->isPost()) {
            $adArray = $this->request->getPost()->toArray();

            if(!in_array($adArray['theme_ad_type'], array('image','slide'))) {
                $adArray['theme_ad_body'] = $adArray['ad_' . $adArray['theme_ad_type']];
            }
            //广告基础内容插入
            if(empty($adArray['theme_ad_id']))  $adId = $this->getDbshopTable('ThemeAdTable')->addAd($adArray);
            else $adId = $adArray['theme_ad_id'];

            //图片广告处理
            if($adArray['theme_ad_type'] == 'image') {
                $adImage = $this->getServiceLocator()->get('shop_other_upload')->adImageUpload('ad_image', (isset($adArray['old_ad_image']) ? $adArray['old_ad_image'] : ''), '', '', 'theme');
                if($adImage['image'] != '')$this->getDbshopTable('ThemeAdTable')->updateAd(array('theme_ad_body'=>$adImage['image']), array('theme_ad_id'=>$adId, 'item_id'=>$adArray['item_id']));
            }

            //广告基础内容编辑（当为编辑状态时）
            if($adArray['theme_ad_id'] > 0) $this->getDbshopTable('ThemeAdTable')->updateAd($adArray, array('theme_ad_id'=>$adId, 'item_id'=>$adArray['item_id']));

            //幻灯片广告处理
            if($adArray['theme_ad_type'] == 'slide') {
                //当为编辑状态时
                if($adArray['theme_ad_id'] > 0) $this->getDbshopTable('ThemeAdSlideTable')->delSlideData(array('theme_ad_id'=>$adId, 'item_id'=>$adArray['item_id']));

                for($i=1; $i<=5; $i++) {
                    if($_FILES['ad_slide_image_' . $i]['name'] != '' or (isset($adArray['old_ad_slide_image_' . $i]) and $adArray['old_ad_slide_image_' . $i] != '')) {
                        $slideArray = array();
                        $slideImage = $this->getServiceLocator()->get('shop_other_upload')->adImageUpload('ad_slide_image_' . $i, (isset($adArray['old_ad_slide_image_' . $i]) ? $adArray['old_ad_slide_image_' . $i] : ''), '', '', 'theme');

                        $slideArray['theme_ad_id']          = $adId;
                        $slideArray['theme_ad_slide_info']  = $adArray['ad_slide_text_' . $i];
                        $slideArray['theme_ad_slide_image'] = $slideImage['image'];
                        $slideArray['theme_ad_slide_sort']  = $adArray['ad_slide_sort_' . $i];
                        $slideArray['theme_ad_slide_url']   = $adArray['ad_slide_url_' . $i];
                        $slideArray['item_id']              = $adArray['item_id'];
                        $this->getDbshopTable('ThemeAdSlideTable')->addAdSlide($slideArray);
                    }
                }
                unset($slideArray, $slideImage);
            }
            $array['success_msg'] = $this->getDbshopLang()->translate('信息更新成功！');
        }

        $itemInfo = $this->getDbshopTable('ThemeItemTable')->infoItem(array('theme_id'=>$themeId, 'item_id'=>$itemId));
        if(empty($itemInfo)) $this->redirect()->toRoute('admintheme/default');
        $array['item_info'] = $itemInfo;

        $adInfo = $this->getDbshopTable('ThemeAdTable')->infoAd(array('item_id'=>$itemId));
        if(is_array($adInfo) and !empty($adInfo)) {
            $array['ad'] = $adInfo;

            if($adInfo['theme_ad_type'] == 'slide') {
                $array['slide_array'] = $this->getDbshopTable('ThemeAdSlideTable')->listAdSlide(array('item_id'=>$itemId, 'theme_ad_id'=>$adInfo['theme_ad_id']));
            }
        }

        $iniReader = new Ini();
        $themeAdIni= array();
        $filePath  = DBSHOP_PATH . '/module/Theme/view/theme/';
        if(file_exists($filePath . $itemInfo->theme_template . '/' . $itemInfo->theme_template . '.ini')) {
            $themeTemplateArray = $iniReader->fromFile($filePath . $itemInfo->theme_template . '/' . $itemInfo->theme_template . '.ini');
            if(isset($themeTemplateArray['ad_set']) and !empty($themeTemplateArray['ad_set'])) {
                foreach ($themeTemplateArray['ad_set'] as $value) {
                    $themeAdIni[$value['code']] = $value;
                }
            }
        }
        $array['ad_ini'] = $themeAdIni;

        return $array;
    }
    /**
     * 清楚广告
     */
    public function adClearAction()
    {
        $delState= $this->getDbshopLang()->translate('广告清除失败！');
        $themeAdId  = $this->request->getPost('theme_ad_id');    //广告id
        $itemId     = $this->request->getPost('item_id');

        $adInfo  = $this->getDbshopTable('ThemeAdTable')->infoAd(array('theme_ad_id'=>$themeAdId, 'item_id'=>$itemId));
        if(!empty($adInfo)) {
            if($adInfo['theme_ad_type'] == 'image' and $adInfo['theme_ad_body'] != '') {
                @unlink(DBSHOP_PATH . $adInfo['theme_ad_body']);
            }
            if($adInfo['theme_ad_type'] == 'slide') $this->getDbshopTable('ThemeAdSlideTable')->delAdSlide(array('theme_ad_id'=>$themeAdId, 'item_id'=>$itemId));
            $this->getDbshopTable('ThemeAdTable')->delAd(array('theme_ad_id'=>$themeAdId, 'item_id'=>$itemId));
            $delState = 'true';
        }
        exit($delState);
    }
    /**
     * 删除幻灯片图片
     */
    public function delSlideImageAction()
    {
        $delState   = 'false';
        $themeAdId  = (int) $this->request->getPost('theme_ad_id');
        $slideImage = $this->request->getPost('image_path');

        if($themeAdId > 0 and !empty($slideImage)) {
            $where = array('theme_ad_id'=>$themeAdId, 'theme_ad_slide_image'=>$slideImage);
            $slideInfo = $this->getDbshopTable('ThemeAdSlideTable')->infoAdSlide($where);
            if(isset($slideInfo->theme_ad_id) and $slideInfo->theme_ad_id > 0) {
                if($slideInfo->theme_ad_slide_image != '') @unlink(DBSHOP_PATH . $slideInfo->dbapi_ad_slide_image);
                $this->getDbshopTable('ThemeAdSlideTable')->delAdSlide($where);
                $delState = md5($slideImage);
            }
        }
        exit($delState);
    }
    /**
     * 文章设置
     * @return array
     */
    public function cmsListAction()
    {
        $array = array();

        return $array;
    }
    /**
     * 专题模板
     */
    public function templateAction()
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