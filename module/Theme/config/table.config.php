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
    'ThemeTable'        => function  () { return new \Theme\Model\ThemeTable(); },
    'ThemeItemTable'    => function  () { return new \Theme\Model\ThemeItemTable(); },
    'ThemeAdTable'      => function  () { return new \Theme\Model\ThemeAdTable(); },
    'ThemeAdSlideTable' => function  () { return new \Theme\Model\ThemeAdSlideTable(); },
    'ThemeGoodsTable'   => function  () { return new \Theme\Model\ThemeGoodsTable(); },
    'ThemeCmsTable'     => function  () { return new \Theme\Model\ThemeCmsTable(); },
);