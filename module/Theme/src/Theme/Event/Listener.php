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

namespace Theme\Event;

use Zend\EventManager\Event;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;

class Listener implements ListenerAggregateInterface
{
    protected $listeners = array();

    /**
     * @param EventManagerInterface $events
     */
    public function attach(EventManagerInterface $events)
    {
        $shareEvents = $events->getSharedManager();

        //后台
        $this->listeners[] = $shareEvents->attach('Goods\Controller\GoodsController', 'goods.del.backstage.post',
            array($this, 'onDelThemeGoods')
        );

    }
    /**
     * @param EventManagerInterface $events
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }
    /**
     * 删除客户组价格
     * @param Event $e
     */
    public function onDelThemeGoods(Event $e)
    {
        $values = $e->getParam('values');
        if(!empty($values)) {
            $themeGoodsTable = $e->getTarget()->getServiceLocator()->get('ThemeGoodsTable');
            $themeGoodsTable->delGoods(array('goods_id'=>$values));
        }
    }

}