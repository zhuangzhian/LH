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

return array(
    'type'    => 'Literal',
    'options' => array(
        // Change this to something specific to your module
        'route'    => '/home/coupon',
        'defaults' => array(
            // Change this value to reflect the namespace in which
            // the controllers for your module are found
            '__NAMESPACE__' => 'Shopfront\Controller',
            'controller'    => 'Homecoupon',
            'action'        => 'index',
        ),
    ),
    'may_terminate' => true,
    'child_routes' => array(
        // This route is a sane default when developing a module;
        // as you solidify the routes for your module, however,
        // you may want to remove it and replace it with more
        // specific routes.
        'default' => array(
            'type'    => 'Segment',
            'options' => array(
                'route'    => '[/:action][/:id]',
                'constraints' => array(
                    'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                ),
                'defaults' => array(
                ),
            ),
            'may_terminate' => true,
            'child_routes' => array(
                'coupon_page' => array(
                    'type' => 'Segment',
                    'options' => array(
                        'route' => '[/:page]',
                        'constraints' => array(
                        ),
                        'defaults' => array(
                            'page' => 1
                        )
                    )
                )
            )
        ),
    ),
);