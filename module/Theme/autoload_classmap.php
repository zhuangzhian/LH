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

return array(
  'Theme\Module'                        => __DIR__ . '/Module.php',
  'Theme\Controller\ThemeController'    => __DIR__ . '/src/Theme/Controller/ThemeController.php',
  'Theme\Controller\IndexController'    => __DIR__ . '/src/Theme/Controller/IndexController.php',

  'Theme\Model\Theme'                   => __DIR__ . '/src/Theme/Model/Theme.php',
  'Theme\Model\ThemeTable'              => __DIR__ . '/src/Theme/Model/ThemeTable.php',
  'Theme\Model\ThemeItem'               => __DIR__ . '/src/Theme/Model/ThemeItem.php',
  'Theme\Model\ThemeItemTable'          => __DIR__ . '/src/Theme/Model/ThemeItemTable.php',
  'Theme\Model\ThemeAd'                 => __DIR__ . '/src/Theme/Model/ThemeAd.php',
  'Theme\Model\ThemeAdTable'            => __DIR__ . '/src/Theme/Model/ThemeAdTable.php',
  'Theme\Model\ThemeAdSlide'            => __DIR__ . '/src/Theme/Model/ThemeAdSlide.php',
  'Theme\Model\ThemeAdSlideTable'       => __DIR__ . '/src/Theme/Model/ThemeAdSlideTable.php',
  'Theme\Model\ThemeGoods'              => __DIR__ . '/src/Theme/Model/ThemeGoods.php',
  'Theme\Model\ThemeGoodsTable'         => __DIR__ . '/src/Theme/Model/ThemeGoodsTable.php',
  'Theme\Model\ThemeCms'                => __DIR__ . '/src/Theme/Model/ThemeCms.php',
  'Theme\Model\ThemeCmsTable'           => __DIR__ . '/src/Theme/Model/ThemeCmsTable.php',
);
