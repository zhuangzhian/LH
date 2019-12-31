<?php
return array (
  'controllers' => 
  array (
    'invokables' => 
    array (
      'Install\\Controller\\Install' => 'Install\\Controller\\InstallController',
      'Admin\\Controller\\Admin' => 'Admin\\Controller\\AdminController',
      'Admin\\Controller\\Home' => 'Admin\\Controller\\HomeController',
      'User\\Controller\\User' => 'User\\Controller\\UserController',
      'User\\Controller\\Usergroup' => 'User\\Controller\\UsergroupController',
      'User\\Controller\\Userintegration' => 'User\\Controller\\UserintegrationController',
      'User\\Controller\\Integral' => 'User\\Controller\\IntegralController',
      'User\\Controller\\Usermoney' => 'User\\Controller\\UsermoneyController',
      'Goods\\Controller\\Goods' => 'Goods\\Controller\\GoodsController',
      'Goods\\Controller\\Brand' => 'Goods\\Controller\\BrandController',
      'Goods\\Controller\\Class' => 'Goods\\Controller\\ClassController',
      'Goods\\Controller\\Comment' => 'Goods\\Controller\\CommentController',
      'Goods\\Controller\\Ask' => 'Goods\\Controller\\AskController',
      'Goods\\Controller\\Tag' => 'Goods\\Controller\\TagController',
      'Goods\\Controller\\Attribute' => 'Goods\\Controller\\AttributeController',
      'Goods\\Controller\\Promotions' => 'Goods\\Controller\\PromotionsController',
      'Goods\\Controller\\Coupon' => 'Goods\\Controller\\CouponController',
      'Region\\Controller\\Region' => 'Region\\Controller\\RegionController',
      'Email\\Controller\\Email' => 'Email\\Controller\\EmailController',
      'System\\Controller\\System' => 'System\\Controller\\SystemController',
      'System\\Controller\\Online' => 'System\\Controller\\OnlineController',
      'System\\Controller\\Optimization' => 'System\\Controller\\OptimizationController',
      'Package\\Controller\\Package' => 'Package\\Controller\\PackageController',
      'Upload\\Controller\\Upload' => 'Upload\\Controller\\UploadController',
      'Express\\Controller\\Express' => 'Express\\Controller\\ExpressController',
      'Payment\\Controller\\Payment' => 'Payment\\Controller\\PaymentController',
      'Stock\\Controller\\Stock' => 'Stock\\Controller\\StockController',
      'Stock\\Controller\\State' => 'Stock\\Controller\\StateController',
      'Orders\\Controller\\Orders' => 'Orders\\Controller\\OrdersController',
      'Cms\\Controller\\Cms' => 'Cms\\Controller\\CmsController',
      'Cms\\Controller\\Class' => 'Cms\\Controller\\ClassController',
      'Currency\\Controller\\Currency' => 'Currency\\Controller\\CurrencyController',
      'Links\\Controller\\Links' => 'Links\\Controller\\LinksController',
      'Ad\\Controller\\Ad' => 'Ad\\Controller\\AdController',
      'Dbsql\\Controller\\Dbsql' => 'Dbsql\\Controller\\DbsqlController',
      'Operlog\\Controller\\Operlog' => 'Operlog\\Controller\\OperlogController',
      'Errorlog\\Controller\\Errorlog' => 'Errorlog\\Controller\\ErrorlogController',
      'Navigation\\Controller\\Navigation' => 'Navigation\\Controller\\NavigationController',
      'Template\\Controller\\Template' => 'Template\\Controller\\TemplateController',
      'Template\\Controller\\Phonetemplate' => 'Template\\Controller\\PhonetemplateController',
      'Analytics\\Controller\\Analytics' => 'Analytics\\Controller\\AnalyticsController',
      'Plugin\\Controller\\Index' => 'Plugin\\Controller\\IndexController',
      'Shopfront\\Controller\\Index' => 'Shopfront\\Controller\\IndexController',
      'Shopfront\\Controller\\Home' => 'Shopfront\\Controller\\HomeController',
      'Shopfront\\Controller\\User' => 'Shopfront\\Controller\\UserController',
      'Shopfront\\Controller\\Goodslist' => 'Shopfront\\Controller\\GoodslistController',
      'Shopfront\\Controller\\Goods' => 'Shopfront\\Controller\\GoodsController',
      'Shopfront\\Controller\\Cart' => 'Shopfront\\Controller\\CartController',
      'Shopfront\\Controller\\Order' => 'Shopfront\\Controller\\OrderController',
      'Shopfront\\Controller\\Homeaddress' => 'Shopfront\\Controller\\HomeaddressController',
      'Shopfront\\Controller\\Article' => 'Shopfront\\Controller\\ArticleController',
      'Shopfront\\Controller\\Homefavorites' => 'Shopfront\\Controller\\HomefavoritesController',
      'Shopfront\\Controller\\Homemoney' => 'Shopfront\\Controller\\HomemoneyController',
      'Shopfront\\Controller\\Brand' => 'Shopfront\\Controller\\BrandController',
      'Shopfront\\Controller\\Homerefund' => 'Shopfront\\Controller\\HomerefundController',
      'Shopfront\\Controller\\Homecoupon' => 'Shopfront\\Controller\\HomecouponController',
      'Mobile\\Controller\\Index' => 'Mobile\\Controller\\IndexController',
      'Mobile\\Controller\\Class' => 'Mobile\\Controller\\ClassController',
      'Mobile\\Controller\\Cart' => 'Mobile\\Controller\\CartController',
      'Mobile\\Controller\\Goods' => 'Mobile\\Controller\\GoodsController',
      'Mobile\\Controller\\User' => 'Mobile\\Controller\\UserController',
      'Mobile\\Controller\\Home' => 'Mobile\\Controller\\HomeController',
      'Mobile\\Controller\\Order' => 'Mobile\\Controller\\OrderController',
      'Mobile\\Controller\\Address' => 'Mobile\\Controller\\AddressController',
      'Mobile\\Controller\\Favorites' => 'Mobile\\Controller\\FavoritesController',
      'Mobile\\Controller\\Article' => 'Mobile\\Controller\\ArticleController',
      'Mobile\\Controller\\Wx' => 'Mobile\\Controller\\WxController',
      'Theme\\Controller\\Index' => 'Theme\\Controller\\IndexController',
      'Theme\\Controller\\Theme' => 'Theme\\Controller\\ThemeController',
    ),
  ),
  'router' => 
  array (
    'routes' => 
    array (
      'install' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/install',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Install\\Controller',
            'controller' => 'Install',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '/[:controller[/:action]]',
              'constraints' => 
              array (
                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
              ),
            ),
            'may_terminate' => true,
            'child_routes' => 
            array (
              'install_step' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '[/:step]',
                  'constraints' => 
                  array (
                  ),
                  'defaults' => 
                  array (
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
      'admin' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/admin',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Admin\\Controller',
            'controller' => 'Admin',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/[:action]]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
                'action' => 'index',
              ),
            ),
            'may_terminate' => true,
            'child_routes' => 
            array (
              'admin_group_id' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '[/:admin_group_id]',
                ),
              ),
              'admin_id' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '/admin_id[/:admin_id][/:check_type]',
                ),
              ),
            ),
          ),
        ),
      ),
      'adminHome' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/admin/home',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Admin\\Controller',
            'controller' => 'home',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/[:action]]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
                'action' => 'index',
              ),
            ),
          ),
        ),
      ),
      'user' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/admin/user',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'User\\Controller',
            'controller' => 'User',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:action]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
            ),
            'may_terminate' => true,
            'child_routes' => 
            array (
              'user_id' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '[/:user_id]',
                ),
                'may_terminate' => true,
                'child_routes' => 
                array (
                  'other-page' => 
                  array (
                    'type' => 'Segment',
                    'options' => 
                    array (
                      'route' => '/other[/:page]',
                    ),
                  ),
                ),
              ),
              'page' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '/page[/:page]',
                  'constraints' => 
                  array (
                    'page' => '[0-9_-]+',
                  ),
                  'defaults' => 
                  array (
                    'page' => 1,
                  ),
                ),
              ),
              'mail-log-page' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '/mail[/:user_id][/:page]',
                  'constraints' => 
                  array (
                    'page' => '[0-9_-]+',
                  ),
                  'defaults' => 
                  array (
                    'page' => 1,
                  ),
                ),
              ),
              'reg-extend' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '/reg-e[/:field_id]',
                ),
              ),
            ),
          ),
        ),
      ),
      'usergroup' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/admin/user/group',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'User\\Controller',
            'controller' => 'Usergroup',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:action]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
            ),
            'may_terminate' => true,
            'child_routes' => 
            array (
              'user_group_id' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '[/:user_group_id]',
                ),
              ),
            ),
          ),
        ),
      ),
      'usermoney' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/admin/user/money',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'User\\Controller',
            'controller' => 'Usermoney',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:action]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
            ),
            'may_terminate' => true,
            'child_routes' => 
            array (
              'money_log_page' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '/m-log-page[/:page]',
                ),
              ),
            ),
          ),
        ),
      ),
      'userintegration' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/admin/user/integration',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'User\\Controller',
            'controller' => 'Userintegration',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:action][/:integrationtype]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
            ),
          ),
        ),
      ),
      'integral' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/admin/user/integral',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'User\\Controller',
            'controller' => 'Integral',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:action]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
            ),
            'may_terminate' => true,
            'child_routes' => 
            array (
              'integral_rule_id' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '[/:integral_rule_id]',
                ),
              ),
              'integral_type_id' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '/typeId[/:integral_type_id]',
                ),
              ),
              'integrallog_page' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '/ingegrallogpage[/:page]',
                ),
              ),
            ),
          ),
        ),
      ),
      'brand' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/admin/goods/brand',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Goods\\Controller',
            'controller' => 'Brand',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:action][/:brand_id][/:page]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
            ),
          ),
        ),
      ),
      'attribute' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/admin/goods/attribute',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Goods\\Controller',
            'controller' => 'Attribute',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:action]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
            ),
            'may_terminate' => true,
            'child_routes' => 
            array (
              'attribute-id' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '[/:attribute_id]',
                  'constraints' => 
                  array (
                  ),
                ),
                'may_terminate' => true,
                'child_routes' => 
                array (
                  'attribute-value-id' => 
                  array (
                    'type' => 'Segment',
                    'options' => 
                    array (
                      'route' => '/value_id[/:value_id]',
                      'constraints' => 
                      array (
                      ),
                    ),
                  ),
                ),
              ),
              'attribute-group-id' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '/attr_group_id[/:attribute_group_id]',
                  'constraints' => 
                  array (
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
      'tag' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/admin/goods/tag',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Goods\\Controller',
            'controller' => 'Tag',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:action][/:tag_id][/:page]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
            ),
          ),
          'goods_tag_group' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '/taggroup[/:action][/:tag_group_id]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
            ),
          ),
        ),
      ),
      'comment' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/admin/goods/comment',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Goods\\Controller',
            'controller' => 'Comment',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:action]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
            ),
            'may_terminate' => true,
            'child_routes' => 
            array (
              'comment-page' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '/page[/:page]',
                  'constraints' => 
                  array (
                    'page' => '[0-9_-]+',
                  ),
                  'defaults' => 
                  array (
                    'page' => 1,
                  ),
                ),
              ),
              'comment-id' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '/comment_id[/:comment_id][/:goods_id]',
                  'constraints' => 
                  array (
                  ),
                ),
              ),
              'goods-id' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '[/:goods_id][/:page]',
                  'constraints' => 
                  array (
                  ),
                  'defaults' => 
                  array (
                    'page' => 1,
                  ),
                ),
              ),
              'user-name' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '[/:user_name]/com_page[/:page]',
                  'constraints' => 
                  array (
                  ),
                  'defaults' => 
                  array (
                    'page' => 1,
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
      'ask' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/admin/goods/ask',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Goods\\Controller',
            'controller' => 'Ask',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'segment',
            'options' => 
            array (
              'route' => '[/:action]',
            ),
            'may_terminate' => true,
            'child_routes' => 
            array (
              'ask-page' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '/askpage[/:page]',
                  'constraints' => 
                  array (
                    'page' => '[0-9_-]+',
                  ),
                  'defaults' => 
                  array (
                    'page' => 1,
                  ),
                ),
              ),
              'ask-id' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '/askid[/:askid]',
                  'constraints' => 
                  array (
                    'page' => '[0-9_-]+',
                  ),
                  'defaults' => 
                  array (
                    'page' => 1,
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
      'class' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/admin/goods/class',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Goods\\Controller',
            'controller' => 'Class',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'segment',
            'options' => 
            array (
              'route' => '[/:action]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
            ),
            'may_terminate' => true,
            'child_routes' => 
            array (
              'class_id' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '[/:class_id]',
                ),
              ),
              'top_class_id' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '/c/sub_class[/:top_class_id]',
                ),
              ),
              'page' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '[/:class_id][/:page]',
                  'constraints' => 
                  array (
                    'page' => '[0-9_-]+',
                  ),
                  'defaults' => 
                  array (
                    'page' => 1,
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
      'goods' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/admin/goods/goods',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Goods\\Controller',
            'controller' => 'Goods',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:action]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
                'action' => 'index',
              ),
            ),
            'may_terminate' => true,
            'child_routes' => 
            array (
              'goods_id' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '[/:goods_id][/:page]',
                  'constraints' => 
                  array (
                    'goods_id' => '[0-9_-]+',
                  ),
                ),
                'defaults' => 
                array (
                  'controller' => 'Goods',
                  'action' => 'index',
                ),
              ),
              'page' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '/page[/:page]',
                  'constraints' => 
                  array (
                    'page' => '[0-9_-]+',
                  ),
                  'defaults' => 
                  array (
                    'page' => 1,
                  ),
                ),
              ),
              'goods_class_id' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '/class_id[/:goods_class_id]',
                ),
                'defaults' => 
                array (
                  'action' => 'index',
                ),
              ),
            ),
          ),
        ),
      ),
      'promotions' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/admin/goods/promotions',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Goods\\Controller',
            'controller' => 'Promotions',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'segment',
            'options' => 
            array (
              'route' => '[/:action]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
            ),
            'may_terminate' => true,
            'child_routes' => 
            array (
              'promotions_id' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '[/:promotions_id]',
                ),
              ),
            ),
          ),
        ),
      ),
      'coupon' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/admin/goods/coupon',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Goods\\Controller',
            'controller' => 'Coupon',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'segment',
            'options' => 
            array (
              'route' => '[/:action][/:id]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
            ),
            'may_terminate' => true,
            'child_routes' => 
            array (
              'coupon_page' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '/couponpage[/:page]',
                ),
              ),
            ),
          ),
        ),
      ),
      'region' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/admin/region',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Region\\Controller',
            'controller' => 'Region',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:action]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
                'action' => 'index',
              ),
            ),
            'may_terminate' => true,
            'child_routes' => 
            array (
              'region_id' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '[/:region_id]',
                ),
              ),
              'region_top_id' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '/top_id[/:region_top_id]',
                ),
              ),
              'region_type' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '/select[/:region_type]',
                ),
              ),
            ),
          ),
        ),
      ),
      'email' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/admin/email',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Email\\Controller',
            'controller' => 'Email',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:action]',
              'constraints' => 
              array (
                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
              ),
            ),
          ),
        ),
      ),
      'system' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/admin/system',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'System\\Controller',
            'controller' => 'System',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:action]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
                'action' => 'index',
              ),
            ),
          ),
        ),
      ),
      'optimization' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/admin/optimization',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'System\\Controller',
            'controller' => 'Optimization',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:action]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
                'action' => 'index',
              ),
            ),
          ),
        ),
      ),
      'online' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/admin/online',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'System\\Controller',
            'controller' => 'Online',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:action]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
            ),
            'may_terminate' => true,
            'child_routes' => 
            array (
              'online_id' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '[/:online_id]',
                ),
              ),
              'online_group_id' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '/online_group[/:online_group_id]',
                ),
              ),
            ),
          ),
        ),
      ),
      'package' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/admin/package',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Package\\Controller',
            'controller' => 'Package',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '/[:controller[/:action]]',
              'constraints' => 
              array (
                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
                'controller' => 'Package',
                'action' => 'index',
              ),
            ),
            'may_terminate' => true,
            'child_routes' => 
            array (
              'package_id' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '[/:package_id]',
                ),
              ),
            ),
          ),
        ),
      ),
      'upload' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/admin/filemanage',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Upload\\Controller',
            'controller' => 'Upload',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:action]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
              ),
            ),
          ),
        ),
      ),
      'express' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/admin/express',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Express\\Controller',
            'controller' => 'Express',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:action]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
                'action' => 'index',
              ),
            ),
            'may_terminate' => true,
            'child_routes' => 
            array (
              'express-id' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '[/:express_id]',
                  'constraints' => 
                  array (
                    'express_id' => '[0-9_-]+',
                  ),
                ),
              ),
              'express-name-code' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '/name_code[/:express_code]',
                  'constraints' => 
                  array (
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
      'payment' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/admin/payment',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Payment\\Controller',
            'controller' => 'Payment',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:action][/:paytype]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
              ),
            ),
          ),
        ),
      ),
      'stock' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/admin/stock',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Stock\\Controller',
            'controller' => 'Stock',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:action]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
              ),
            ),
          ),
        ),
      ),
      'stock_state' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/admin/system/stock/state',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Stock\\Controller',
            'controller' => 'State',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:action][/:stock_state_id]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
              ),
            ),
          ),
        ),
      ),
      'orders' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/admin/orders',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Orders\\Controller',
            'controller' => 'Orders',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '/[:controller[/:action]]',
              'constraints' => 
              array (
                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
              ),
            ),
            'may_terminate' => true,
            'child_routes' => 
            array (
              'order_id' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '/order_id/[:order_id[/:page]]',
                  'constraints' => 
                  array (
                    'page' => '[0-9_-]+',
                  ),
                  'defaults' => 
                  array (
                    'page' => 1,
                  ),
                ),
              ),
              'buyer-id' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '[/:buyer_id]/b_page[/:page]',
                  'constraints' => 
                  array (
                    'page' => '[0-9_-]+',
                  ),
                  'defaults' => 
                  array (
                    'page' => 1,
                  ),
                ),
              ),
              'refund-id' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '/refundId[/:refund_id]/repage[/:page]',
                  'constraints' => 
                  array (
                    'page' => '[0-9_-]+',
                  ),
                  'defaults' => 
                  array (
                    'page' => 1,
                  ),
                ),
              ),
              'order-express-id' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '/express[/:express_id]/e_page[/:page]',
                  'constraints' => 
                  array (
                    'page' => '[0-9_-]+',
                  ),
                  'defaults' => 
                  array (
                    'page' => 1,
                  ),
                ),
              ),
              'page' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '[/:page]',
                  'constraints' => 
                  array (
                    'page' => '[0-9_-]+',
                  ),
                  'defaults' => 
                  array (
                    'page' => 1,
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
      'cms' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/admin/cms',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Cms\\Controller',
            'controller' => 'Cms',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:controller[/:action]]',
              'constraints' => 
              array (
                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
              ),
            ),
            'may_terminate' => true,
            'child_routes' => 
            array (
              'article_class_id' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '[/:article_class_id]',
                  'constraints' => 
                  array (
                  ),
                  'defaults' => 
                  array (
                  ),
                ),
              ),
              'article_id' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '/article[/:article_id][/:page]',
                  'constraints' => 
                  array (
                    'article_id' => '[0-9_-]+',
                  ),
                  'defaults' => 
                  array (
                    'article_id' => 1,
                  ),
                ),
              ),
              'article_page' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '/page[/:page]',
                  'constraints' => 
                  array (
                    'page' => '[0-9_-]+',
                  ),
                  'defaults' => 
                  array (
                    'page' => 1,
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
      'currency' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/admin/currency',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Currency\\Controller',
            'controller' => 'Currency',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:controller[/:action]]',
              'constraints' => 
              array (
                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
              ),
            ),
            'may_terminate' => true,
            'child_routes' => 
            array (
              'currency_id' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '[/:currency_id]',
                  'constraints' => 
                  array (
                    'currency_id' => '[0-9_-]+',
                  ),
                  'defaults' => 
                  array (
                    'currency_id' => 1,
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
      'links' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/admin/links',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Links\\Controller',
            'controller' => 'Links',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:action]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
              ),
            ),
            'may_terminate' => true,
            'child_routes' => 
            array (
              'links_id' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '[/:links_id]',
                  'constraints' => 
                  array (
                    'links_id' => '[0-9_-]+',
                  ),
                  'defaults' => 
                  array (
                    'links_id' => 1,
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
      'ad' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/admin/ad',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Ad\\Controller',
            'controller' => 'Ad',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:action]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
              ),
            ),
            'may_terminate' => true,
            'child_routes' => 
            array (
              'ad-type' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '[/:ad_type][/:ad_id]',
                  'constraints' => 
                  array (
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
      'dbsql' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/admin/dbsql',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Dbsql\\Controller',
            'controller' => 'Dbsql',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:action]',
              'constraints' => 
              array (
                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
              ),
            ),
            'may_terminate' => true,
            'child_routes' => 
            array (
              'backup' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '[/:sql_file_name][/:vol][/:type]',
                ),
              ),
            ),
          ),
        ),
      ),
      'operlog' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/admin/operlog',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Operlog\\Controller',
            'controller' => 'Operlog',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:action]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
              ),
            ),
            'may_terminate' => true,
            'child_routes' => 
            array (
              'operlog_page' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '[/:page]',
                  'constraints' => 
                  array (
                  ),
                  'defaults' => 
                  array (
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
      'errorlog' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/admin/errorlog',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Errorlog\\Controller',
            'controller' => 'Errorlog',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:controller][/:action]',
              'constraints' => 
              array (
                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
              ),
            ),
          ),
        ),
      ),
      'navigation' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/admin/navigation',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Navigation\\Controller',
            'controller' => 'Navigation',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/[:action]]',
              'constraints' => 
              array (
                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
              ),
            ),
            'may_terminate' => true,
            'child_routes' => 
            array (
              'navigation_id' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '[/:navigation_id]',
                ),
              ),
            ),
          ),
        ),
      ),
      'template' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/admin/template',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Template\\Controller',
            'controller' => 'Template',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '/[:controller[/:action]]',
              'constraints' => 
              array (
                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
              ),
            ),
            'may_terminate' => true,
            'child_routes' => 
            array (
              'templateinfo' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '/template-name[/:template_name]',
                  'constraints' => 
                  array (
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
      'phonetemplate' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/admin/phonetemplate',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Template\\Controller',
            'controller' => 'Phonetemplate',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:action]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
                'action' => 'index',
              ),
            ),
            'may_terminate' => true,
            'child_routes' => 
            array (
              'p-templateinfo' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '/p-template-name[/:p_template_name]',
                  'constraints' => 
                  array (
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
      'analytics' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/admin/analytics',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Analytics\\Controller',
            'controller' => 'Analytics',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:controller[/:action]]',
              'constraints' => 
              array (
                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
                'controller' => 'Analytics',
                'action' => 'index',
              ),
            ),
            'may_terminate' => true,
            'child_routes' => 
            array (
              'page' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '/page[/:page]',
                  'constraints' => 
                  array (
                    'page' => '[0-9_-]+',
                  ),
                  'defaults' => 
                  array (
                    'page' => 1,
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
      'plugin' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/admin/plugin',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Plugin\\Controller',
            'controller' => 'Index',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:action]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
                'action' => 'index',
              ),
            ),
          ),
        ),
      ),
      'shopfront' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Shopfront\\Controller',
            'controller' => 'Index',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[indexother/:action]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
              ),
            ),
            'may_terminate' => true,
            'child_routes' => 
            array (
              'captcha-check' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '[/:captcha_check]',
                  'constraints' => 
                  array (
                  ),
                  'defaults' => 
                  array (
                  ),
                ),
              ),
              'currency-code' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '/currency[/:code]',
                  'constraints' => 
                  array (
                  ),
                  'defaults' => 
                  array (
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
      'frontuser' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/user',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Shopfront\\Controller',
            'controller' => 'User',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:action]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
              ),
            ),
            'may_terminate' => true,
            'child_routes' => 
            array (
              'user_id' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '[/:user_id][/:check_type]',
                ),
              ),
              'other_login_type' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '/loginType[/:login_type]',
                ),
              ),
            ),
          ),
        ),
      ),
      'fronthome' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/home',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Shopfront\\Controller',
            'controller' => 'Home',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:action]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
              ),
            ),
            'may_terminate' => true,
            'child_routes' => 
            array (
              'front-ask-id' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '/askid[/:ask_id]',
                ),
              ),
              'page' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '[/:page]',
                ),
              ),
            ),
          ),
        ),
      ),
      'frontgoodslist' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/list',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Shopfront\\Controller',
            'controller' => 'Goodslist',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:action][/:class_id][/:page]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
                'action' => 'index',
              ),
            ),
          ),
        ),
      ),
      'frontgoods' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/goods',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Shopfront\\Controller',
            'controller' => 'Goods',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:action][/:goods_id][/:class_id][/:page]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
              ),
            ),
          ),
        ),
      ),
      'frontcart' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/cart',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Shopfront\\Controller',
            'controller' => 'Scshop',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:action]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
              ),
            ),
          ),
        ),
      ),
      'frontorder' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/home/order',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Shopfront\\Controller',
            'controller' => 'Order',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:action]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
              ),
            ),
            'may_terminate' => true,
            'child_routes' => 
            array (
              'order_page' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '/state[/:order_state]/orderpage[/:page]',
                  'constraints' => 
                  array (
                  ),
                  'defaults' => 
                  array (
                    'page' => 1,
                  ),
                ),
              ),
              'order_id' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '[/:order_id][/:order_state][/:page]',
                  'constraints' => 
                  array (
                  ),
                ),
              ),
              'order_goods_id' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '/goods[/:order_goods_id][/:order_state][/:page]',
                  'constraints' => 
                  array (
                  ),
                ),
              ),
              'order_state' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '/state[/:order_state]',
                  'constraints' => 
                  array (
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
      'frontaddress' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/home/address',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Shopfront\\Controller',
            'controller' => 'Homeaddress',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:action][/:address_id]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
              ),
            ),
          ),
        ),
      ),
      'frontarticle' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/article',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Shopfront\\Controller',
            'controller' => 'Article',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:action]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
              ),
            ),
            'may_terminate' => true,
            'child_routes' => 
            array (
              'cms_class_id' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '[/:cms_class_id][/:page]',
                  'constraints' => 
                  array (
                  ),
                ),
              ),
              'cms_id' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '/id[/:cms_id]',
                  'constraints' => 
                  array (
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
      'frontfavorites' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/home/favorites',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Shopfront\\Controller',
            'controller' => 'Homefavorites',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:action]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
              ),
            ),
            'may_terminate' => true,
            'child_routes' => 
            array (
              'favorites_page' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '[/:page]',
                  'constraints' => 
                  array (
                  ),
                  'defaults' => 
                  array (
                    'page' => 1,
                  ),
                ),
              ),
              'favorites_id' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '/favorites_id[/:favorites_id]',
                  'constraints' => 
                  array (
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
      'frontbrand' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/brand',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Shopfront\\Controller',
            'controller' => 'Brand',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:action]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
              ),
            ),
            'may_terminate' => true,
            'child_routes' => 
            array (
              'front-brand-id' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '[/:brand_id][/:page]',
                  'constraints' => 
                  array (
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
      'frontmoney' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/home/money',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Shopfront\\Controller',
            'controller' => 'Homemoney',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:action]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
              ),
            ),
            'may_terminate' => true,
            'child_routes' => 
            array (
              'paycheck_id' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '/check_id[/:paycheck_id]',
                ),
              ),
              'm_page' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '[/:page]',
                  'constraints' => 
                  array (
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
      'frontrefund' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/home/refund',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Shopfront\\Controller',
            'controller' => 'Homerefund',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:action]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
                'action' => 'index',
              ),
            ),
            'may_terminate' => true,
            'child_routes' => 
            array (
              'refund_id' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '[/:refund_id]',
                ),
              ),
            ),
          ),
        ),
      ),
      'frontcoupon' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/home/coupon',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Shopfront\\Controller',
            'controller' => 'Homecoupon',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:action][/:id]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
              ),
            ),
            'may_terminate' => true,
            'child_routes' => 
            array (
              'coupon_page' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '[/:page]',
                  'constraints' => 
                  array (
                  ),
                  'defaults' => 
                  array (
                    'page' => 1,
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
      'mobile' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/mobile',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Mobile\\Controller',
            'controller' => 'Index',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '/[:controller[/:action]]',
              'constraints' => 
              array (
                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
                'controller' => 'Index',
              ),
            ),
          ),
        ),
      ),
      'm_class' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/mobile/class',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Mobile\\Controller',
            'controller' => 'Class',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '/[:controller[/:action]][/:class_id][/:page]',
              'constraints' => 
              array (
                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
              ),
            ),
          ),
        ),
      ),
      'm_goods' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/mobile/goods',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Mobile\\Controller',
            'controller' => 'Goods',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:action][/:goods_id][/:class_id][/:page]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
              ),
            ),
          ),
        ),
      ),
      'm_cart' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/mobile/cart',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Mobile\\Controller',
            'controller' => 'Cart',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:action]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
              ),
            ),
          ),
        ),
      ),
      'm_user' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/mobile/user',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Mobile\\Controller',
            'controller' => 'User',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:action]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
              ),
            ),
            'may_terminate' => true,
            'child_routes' => 
            array (
              'user_id' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '[/:user_id][/:check_type]',
                ),
              ),
              'other_login_type' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '/loginType[/:login_type]',
                ),
              ),
            ),
          ),
        ),
      ),
      'm_home' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/mobile/home',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Mobile\\Controller',
            'controller' => 'Home',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:action]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
              ),
            ),
            'may_terminate' => true,
            'child_routes' => 
            array (
              'front-ask-id' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '/askid[/:ask_id]',
                ),
              ),
              'page' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '[/:page]',
                ),
              ),
            ),
          ),
        ),
      ),
      'm_order' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/mobile/home/order',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Mobile\\Controller',
            'controller' => 'Order',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:action]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
              ),
            ),
            'may_terminate' => true,
            'child_routes' => 
            array (
              'order_page' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '/state[/:order_state]/orderpage[/:page]',
                  'constraints' => 
                  array (
                  ),
                  'defaults' => 
                  array (
                    'page' => 1,
                  ),
                ),
              ),
              'order_id' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '[/:order_id][/:order_state][/:page]',
                  'constraints' => 
                  array (
                  ),
                ),
              ),
              'order_goods_id' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '/goods[/:order_goods_id][/:order_state][/:page]',
                  'constraints' => 
                  array (
                  ),
                ),
              ),
              'virtual_goods' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '/viorderid[/:order_id]/vigoodsid[/:goods_id]',
                ),
              ),
              'virtual_goods_page' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '/vigoodspage[/:page]',
                ),
              ),
              'refund_page' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '/refundpage[/:page]',
                ),
              ),
              'order_state' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '/state[/:order_state]',
                  'constraints' => 
                  array (
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
      'm_address' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/mobile/home/address',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Mobile\\Controller',
            'controller' => 'Address',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:action][/:address_id]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
              ),
            ),
          ),
        ),
      ),
      'm_favorites' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/mobile/home/favorites',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Mobile\\Controller',
            'controller' => 'Favorites',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:action]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
              ),
            ),
            'may_terminate' => true,
            'child_routes' => 
            array (
              'favorites_page' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '[/:page]',
                  'constraints' => 
                  array (
                  ),
                  'defaults' => 
                  array (
                    'page' => 1,
                  ),
                ),
              ),
              'favorites_id' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '/favorites_id[/:favorites_id]',
                  'constraints' => 
                  array (
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
      'm_article' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/mobile/article',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Mobile\\Controller',
            'controller' => 'Article',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:action]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
              ),
            ),
            'may_terminate' => true,
            'child_routes' => 
            array (
              'cms_class_id' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '[/:cms_class_id][/:page]',
                  'constraints' => 
                  array (
                  ),
                ),
              ),
              'cms_id' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '/id[/:cms_id]',
                  'constraints' => 
                  array (
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
      'm_wx' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/wxpay',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Mobile\\Controller',
            'controller' => 'Wx',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:action]_',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
                'action' => 'index',
              ),
            ),
            'may_terminate' => true,
            'child_routes' => 
            array (
              'wx_order_id' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '[:order_id].html',
                  'constraints' => 
                  array (
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
      'admintheme' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/admin/theme',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Theme\\Controller',
            'controller' => 'Theme',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:action]',
              'constraints' => 
              array (
                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
                'action' => 'index',
              ),
            ),
          ),
        ),
      ),
      'fronttheme' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/theme',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Theme\\Controller',
            'controller' => 'Index',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Segment',
            'options' => 
            array (
              'route' => '[/:action]',
              'constraints' => 
              array (
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
                'action' => 'index',
              ),
            ),
            'may_terminate' => true,
            'child_routes' => 
            array (
              'title' => 
              array (
                'type' => 'Segment',
                'options' => 
                array (
                  'route' => '[/:title][/]',
                  'constraints' => 
                  array (
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    ),
  ),
  'translator' => 
  array (
    'translation_file_patterns' => 
    array (
      0 => 
      array (
        'type' => 'gettext',
        'base_dir' => 'D:\\wwwroot\\dbshop6\\module\\Install\\config/../language',
        'pattern' => '%s.mo',
      ),
      1 => 
      array (
        'type' => 'gettext',
        'base_dir' => 'D:\\wwwroot\\dbshop6\\module\\Admin\\config/../language',
        'pattern' => '%s.mo',
      ),
      2 => 
      array (
        'type' => 'gettext',
        'base_dir' => 'D:\\wwwroot\\dbshop6\\module\\User\\config/../language',
        'pattern' => '%s.mo',
      ),
      3 => 
      array (
        'type' => 'gettext',
        'base_dir' => 'D:\\wwwroot\\dbshop6\\module\\Goods\\config/../language',
        'pattern' => '%s.mo',
      ),
      4 => 
      array (
        'type' => 'gettext',
        'base_dir' => 'D:\\wwwroot\\dbshop6\\module\\Region\\config/../language',
        'pattern' => '%s.mo',
      ),
      5 => 
      array (
        'type' => 'gettext',
        'base_dir' => 'D:\\wwwroot\\dbshop6\\module\\Email\\config/../language',
        'pattern' => '%s.mo',
      ),
      6 => 
      array (
        'type' => 'gettext',
        'base_dir' => 'D:\\wwwroot\\dbshop6\\module\\System\\config/../language',
        'pattern' => '%s.mo',
      ),
      7 => 
      array (
        'type' => 'gettext',
        'base_dir' => 'D:\\wwwroot\\dbshop6\\module\\Package\\config/../language',
        'pattern' => '%s.mo',
      ),
      8 => 
      array (
        'type' => 'gettext',
        'base_dir' => 'D:\\wwwroot\\dbshop6\\module\\Upload\\config/../language',
        'pattern' => '%s.mo',
      ),
      9 => 
      array (
        'type' => 'gettext',
        'base_dir' => 'D:\\wwwroot\\dbshop6\\module\\Express\\config/../language',
        'pattern' => '%s.mo',
      ),
      10 => 
      array (
        'type' => 'gettext',
        'base_dir' => 'D:\\wwwroot\\dbshop6\\module\\Payment\\config/../language',
        'pattern' => '%s.mo',
      ),
      11 => 
      array (
        'type' => 'gettext',
        'base_dir' => 'D:\\wwwroot\\dbshop6\\module\\Stock\\config/../language',
        'pattern' => '%s.mo',
      ),
      12 => 
      array (
        'type' => 'gettext',
        'base_dir' => 'D:\\wwwroot\\dbshop6\\module\\Orders\\config/../language',
        'pattern' => '%s.mo',
      ),
      13 => 
      array (
        'type' => 'gettext',
        'base_dir' => 'D:\\wwwroot\\dbshop6\\module\\Cms\\config/../language',
        'pattern' => '%s.mo',
      ),
      14 => 
      array (
        'type' => 'gettext',
        'base_dir' => 'D:\\wwwroot\\dbshop6\\module\\Currency\\config/../language',
        'pattern' => '%s.mo',
      ),
      15 => 
      array (
        'type' => 'gettext',
        'base_dir' => 'D:\\wwwroot\\dbshop6\\module\\Links\\config/../language',
        'pattern' => '%s.mo',
      ),
      16 => 
      array (
        'type' => 'gettext',
        'base_dir' => 'D:\\wwwroot\\dbshop6\\module\\Ad\\config/../language',
        'pattern' => '%s.mo',
      ),
      17 => 
      array (
        'type' => 'gettext',
        'base_dir' => 'D:\\wwwroot\\dbshop6\\module\\Dbsql\\config/../language',
        'pattern' => '%s.mo',
      ),
      18 => 
      array (
        'type' => 'gettext',
        'base_dir' => 'D:\\wwwroot\\dbshop6\\module\\Operlog\\config/../language',
        'pattern' => '%s.mo',
      ),
      19 => 
      array (
        'type' => 'gettext',
        'base_dir' => 'D:\\wwwroot\\dbshop6\\module\\Navigation\\config/../language',
        'pattern' => '%s.mo',
      ),
      20 => 
      array (
        'type' => 'gettext',
        'base_dir' => 'D:\\wwwroot\\dbshop6\\module\\Template\\config/../language',
        'pattern' => '%s.mo',
      ),
      21 => 
      array (
        'type' => 'gettext',
        'base_dir' => 'D:\\wwwroot\\dbshop6\\module\\Analytics\\config/../language',
        'pattern' => '%s.mo',
      ),
      22 => 
      array (
        'type' => 'gettext',
        'base_dir' => 'D:\\wwwroot\\dbshop6\\module\\Plugin\\config/../language',
        'pattern' => '%s.mo',
      ),
      23 => 
      array (
        'type' => 'gettext',
        'base_dir' => 'D:\\wwwroot\\dbshop6\\module\\Shopfront\\config/../language/dbmall',
        'pattern' => '%s.mo',
      ),
      24 => 
      array (
        'type' => 'gettext',
        'base_dir' => 'D:\\wwwroot\\dbshop6\\module\\Mobile\\config/../language/default',
        'pattern' => '%s.mo',
      ),
      25 => 
      array (
        'type' => 'gettext',
        'base_dir' => 'D:\\wwwroot\\dbshop6\\module\\Theme\\config/../language',
        'pattern' => '%s.mo',
      ),
    ),
    'locale' => 'zh_CN',
  ),
  'view_manager' => 
  array (
    'template_map' => 
    array (
      'install/layout' => 'D:\\wwwroot\\dbshop6\\module\\Install\\config/../view/install/install/install_layout.phtml',
      'layout/layout' => 'D:\\wwwroot\\dbshop6\\module\\Admin\\config/../view/layout/admin.phtml',
      'layout/header' => 'D:\\wwwroot\\dbshop6\\module\\Admin\\config/../view/layout/admin_header_layout.phtml',
      'layout/footer' => 'D:\\wwwroot\\dbshop6\\module\\Admin\\config/../view/layout/admin_footer_layout.phtml',
      'error/404' => 'D:\\wwwroot\\dbshop6\\module\\Admin\\config/../view/error/404.phtml',
      'error/index' => 'D:\\wwwroot\\dbshop6\\module\\Admin\\config/../view/error/index.phtml',
      'common/pager' => 'D:\\wwwroot\\dbshop6\\module\\Admin\\config/../view/common/pager.phtml',
      'common/ajax-pages' => 'D:\\wwwroot\\dbshop6\\module\\Admin\\config/../view/common/dbshop-ajax-pages.phtml',
      'uploadset/view' => 'D:\\wwwroot\\dbshop6\\module\\System\\config/../../Upload/view/upload/upload/uploadset.phtml',
      'site/layout' => 'D:\\wwwroot\\dbshop6\\module\\Shopfront\\config/../view/dbmall/shopfront/common/index.phtml',
      'site/header' => 'D:\\wwwroot\\dbshop6\\module\\Shopfront\\config/../view/dbmall/shopfront/common/header.phtml',
      'site/footer' => 'D:\\wwwroot\\dbshop6\\module\\Shopfront\\config/../view/dbmall/shopfront/common/footer.phtml',
      'site/kefu' => 'D:\\wwwroot\\dbshop6\\module\\Shopfront\\config/../view/dbmall/shopfront/common/kefu.phtml',
      'site/dbpage' => 'D:\\wwwroot\\dbshop6\\module\\Shopfront\\config/../view/dbmall/shopfront/common/pager.phtml',
      'site/ajax-dbpage' => 'D:\\wwwroot\\dbshop6\\module\\Shopfront\\config/../view/dbmall/shopfront/common/ajax-pages.phtml',
      'site/headermenu' => 'D:\\wwwroot\\dbshop6\\module\\Shopfront\\config/../view/dbmall/shopfront/common/indexmenu.phtml',
      'mobile/layout' => 'D:\\wwwroot\\dbshop6\\module\\Mobile\\config/../view/default/mobile/common/index.phtml',
      'mobile/header' => 'D:\\wwwroot\\dbshop6\\module\\Mobile\\config/../view/default/mobile/common/header.phtml',
      'mobile/footer' => 'D:\\wwwroot\\dbshop6\\module\\Mobile\\config/../view/default/mobile/common/footer.phtml',
      'mobile/ajaxpage' => 'D:\\wwwroot\\dbshop6\\module\\Mobile\\config/../view/default/mobile/common/ajaxpage.phtml',
      'mobile/moreajax' => 'D:\\wwwroot\\dbshop6\\module\\Mobile\\config/../view/default/mobile/common/moreajax.phtml',
      'mobile/page' => 'D:\\wwwroot\\dbshop6\\module\\Mobile\\config/../view/default/mobile/common/page.phtml',
    ),
    'template_path_stack' => 
    array (
      'Install' => 'D:\\wwwroot\\dbshop6\\module\\Install\\config/../view',
      'Admin' => 'D:\\wwwroot\\dbshop6\\module\\Admin\\config/../view',
      'User' => 'D:\\wwwroot\\dbshop6\\module\\User\\config/../view',
      'Goods' => 'D:\\wwwroot\\dbshop6\\module\\Goods\\config/../view',
      'Region' => 'D:\\wwwroot\\dbshop6\\module\\Region\\config/../view',
      'Email' => 'D:\\wwwroot\\dbshop6\\module\\Email\\config/../view',
      'System' => 'D:\\wwwroot\\dbshop6\\module\\System\\config/../view',
      'Package' => 'D:\\wwwroot\\dbshop6\\module\\Package\\config/../view',
      'Upload' => 'D:\\wwwroot\\dbshop6\\module\\Upload\\config/../view',
      'Express' => 'D:\\wwwroot\\dbshop6\\module\\Express\\config/../view',
      'Payment' => 'D:\\wwwroot\\dbshop6\\module\\Payment\\config/../view',
      'Stock' => 'D:\\wwwroot\\dbshop6\\module\\Stock\\config/../view',
      'Orders' => 'D:\\wwwroot\\dbshop6\\module\\Orders\\config/../view',
      'Cms' => 'D:\\wwwroot\\dbshop6\\module\\Cms\\config/../view',
      'Currency' => 'D:\\wwwroot\\dbshop6\\module\\Currency\\config/../view',
      'Links' => 'D:\\wwwroot\\dbshop6\\module\\Links\\config/../view',
      'Ad' => 'D:\\wwwroot\\dbshop6\\module\\Ad\\config/../view',
      'Dbsql' => 'D:\\wwwroot\\dbshop6\\module\\Dbsql\\config/../view',
      'Operlog' => 'D:\\wwwroot\\dbshop6\\module\\Operlog\\config/../view',
      'Errorlog' => 'D:\\wwwroot\\dbshop6\\module\\Errorlog\\config/../view',
      'Navigation' => 'D:\\wwwroot\\dbshop6\\module\\Navigation\\config/../view',
      'Template' => 'D:\\wwwroot\\dbshop6\\module\\Template\\config/../view',
      'Analytics' => 'D:\\wwwroot\\dbshop6\\module\\Analytics\\config/../view',
      'Plugin' => 'D:\\wwwroot\\dbshop6\\module\\Plugin\\config/../view',
      'Shopfront' => 'D:\\wwwroot\\dbshop6\\module\\Shopfront\\config/../view/dbmall',
      'Mobile' => 'D:\\wwwroot\\dbshop6\\module\\Mobile\\config/../view/default',
      'Theme' => 'D:\\wwwroot\\dbshop6\\module\\Theme\\config/../view',
    ),
    'display_not_found_reason' => true,
    'display_exceptions' => true,
    'doctype' => 'HTML5',
    'not_found_template' => 'error/404',
    'exception_template' => 'error/index',
  ),
  'db' => 
  array (
    'driver' => 'Pdo',
    'dsn' => 'mysql:dbname=sql_127_0_0_6;port=3306;host=localhost;charset=utf8',
    'username' => 'sql_127_0_0_6',
    'password' => 'nebYE2dxFK28JXja',
    'driver_options' => 
    array (
      1002 => 'SET sql_mode=(SELECT REPLACE(@@sql_mode,\'ONLY_FULL_GROUP_BY\',\'\'))',
    ),
  ),
  'service_manager' => 
  array (
    'factories' => 
    array (
      'Zend\\Db\\Adapter\\Adapter' => 'Zend\\Db\\Adapter\\AdapterServiceFactory',
      'translator' => 'Zend\\I18n\\Translator\\TranslatorServiceFactory',
    ),
  ),
);