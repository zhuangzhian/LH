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

namespace Express\Common\Service;


class ExpressStateApi
{
    
    public function __construct()
    {

    }
    /** 
     * 快递状态公共输出端口
     * @return Ambigous <\Express\Common\Service\unknown, multitype:, boolean, \Zend\Config\Reader\mixed, string>
     */
    public function getExpressStateContent($expressApi, $expressNameCode, $expressNumber)
    {
        $httpType = ((isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
        $contentArray = array();

        if(file_exists(__DIR__ . '/api/'.$expressApi['name_code'].'.php')) {
            include __DIR__ . '/api/'.$expressApi['name_code'].'.php';
            $className = $expressApi['name_code'].'ExpressApi';
            $expressClass = new $className;
            $contentArray = $expressClass->stateContent($expressApi, $expressNameCode, $expressNumber, $httpType);
        }

        return $contentArray;

    }
}