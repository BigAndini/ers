<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

return array(
    'controllers' => array(
        'invokables' => array(
            'Admin\Controller\Admin'                => 'Admin\Controller\AdminController',
            'Admin\Controller\Tax'                  => 'Admin\Controller\TaxController',
            'Admin\Controller\Product'              => 'Admin\Controller\ProductController',
            'Admin\Controller\ProductPackage'       => 'Admin\Controller\ProductPackageController',
            'Admin\Controller\ProductVariant'       => 'Admin\Controller\ProductVariantController',
            'Admin\Controller\ProductVariantValue'  => 'Admin\Controller\ProductVariantValueController',
            'Admin\Controller\ProductPrice'         => 'Admin\Controller\ProductPriceController',
            'Admin\Controller\Deadline'             => 'Admin\Controller\DeadlineController',
            'Admin\Controller\Agegroup'             => 'Admin\Controller\AgegroupController',
            'Admin\Controller\PaymentType'          => 'Admin\Controller\PaymentTypeController',
            'Admin\Controller\Counter'              => 'Admin\Controller\CounterController',
            'Admin\Controller\User'                 => 'Admin\Controller\UserController',
            'Admin\Controller\Role'                 => 'Admin\Controller\RoleController',
            'Admin\Controller\Order'                => 'Admin\Controller\OrderController',
            'Admin\Controller\Bankaccount'          => 'Admin\Controller\BankaccountController',
            'Admin\Controller\Country'              => 'Admin\Controller\CountryController',
            'Admin\Controller\Test'                 => 'Admin\Controller\TestController',
        ),
    ),
    'navigation' => array(
        'admin_main_nav' => array(
            'home' => array(
                'label' => 'Frontend',
                'route' => 'home',
                'target' => '_blank',
                'resource'  => 'controller/PreReg\Controller\Product',
            ),
            'shop' => array(
                'label' => 'Shop',
                'route' => 'admin',
                'pages' => array(
                    'tax' => array(
                        'label' => 'Tax',
                        'route' => 'admin/tax',
                        'resource'  => 'controller/Admin\Controller\Tax',
                    ),
                    'deadline' => array(
                        'label' => 'Deadline',
                        'route' => 'admin/deadline',
                        'resource'  => 'controller/Admin\Controller\Deadline',
                    ),
                    'agegroup' => array(
                        'label' => 'Agegroup',
                        'route' => 'admin/agegroup',
                        'resource'  => 'controller/Admin\Controller\Agegroup',
                    ),
                    'paymenttype' => array(
                        'label' => 'Payment Type',
                        'route' => 'admin/payment-type',
                        'resource'  => 'controller/Admin\Controller\PaymentType',
                    ),
                    'country' => array(
                        'label' => 'Country',
                        'route' => 'admin/country',
                        'resource'  => 'controller/Admin\Controller\Country',
                    ),
                ),
            ),
            'product' => array(
                'label' => 'Product',
                'route' => 'admin/product',
                #'action' => 'reset',
                'resource'  => 'controller/Admin\Controller\Product',
            ),
            'counter' => array(
                'label' => 'Counter',
                'route' => 'admin/counter',
                'resource'  => 'controller/Admin\Controller\Counter',
            ),
            'user' => array(
                'label' => 'User',
                'route' => 'admin/user',
                'resource'  => 'controller/Admin\Controller\User',
                'pages' => array(
                    'role' => array(
                        'label' => 'Role',
                        'route' => 'admin/role',
                        'resource'  => 'controller/Admin\Controller\Role',
                    ),
                ),
            ),
            'order' => array(
                'label' => 'Order',
                'route' => 'admin/order',
                'resource'  => 'controller/Admin\Controller\Order',
            ),
            'bankaccount' => array(
                'label' => 'Bankaccount',
                'route' => 'admin/bankaccount',
                'resource'  => 'controller/Admin\Controller\Bankaccount',
                'pages' => array(
                    'role' => array(
                        'label' => 'Upload CSV',
                        'route' => 'admin/bankaccount',
                        'action' => 'upload-csv',
                        'resource'  => 'controller/Admin\Controller\Bankaccount',
                    ),
                ),
            ),
        ),
        'admin_top_nav' => array(
            'login' => array(
                'label' => 'Login',
                'route' => 'zfcuser/login',
                #'action' => 'login',
                'resource'  => 'controller/zfcuser:login',
            ),
            'register' => array(
                'label' => 'Register',
                'route' => 'zfcuser/register',
                #'action' => 'register',
                'resource'  => 'controller/zfcuser:register',
            ),
            'profile' => array(
                'label' => 'My Profile',
                'route' => 'zfcuser',
                'action' => '',
                'resource'  => 'controller/zfcuser:index',
            ),
            'logout' => array(
                'label' => 'Logout',
                'route' => 'zfcuser/logout',
                #'action' => 'logout',
                'resource'  => 'controller/zfcuser:logout',
            ),
            'admin' => array(
                'label' => 'AdminPanel',
                'route' => 'admin',
                'resource'  => 'controller/Admin\Controller\Admin',
            ),
        ),
    ),
    'router' => array(
        'routes' => array(
            'admin' => array(
                'type' => 'segment',
                'options' => array(
                    #'route' => '/admin[/]',
                    'route' => '/admin',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Admin',
                        'action' => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'test' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route'    => '/test[/:action][/:id]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'     => '[0-9]+',
                            ),
                            'defaults' => array(
                                'controller' => 'Admin\Controller\Test',
                                'action'     => 'index',
                            ),
                        ),
                    ),
                    'tax' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route'    => '/tax[/:action][/:id]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'     => '[0-9]+',
                            ),
                            'defaults' => array(
                                'controller' => 'Admin\Controller\Tax',
                                'action'     => 'index',
                            ),
                        ),
                    ),
                    'product' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route'    => '/product[/:action][/:id]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'     => '[0-9]+',
                            ),
                            'defaults' => array(
                                'controller' => 'Admin\Controller\Product',
                                'action' => 'index',
                            ),
                        ),
                    ),
                    'deadline' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route'    => '/deadline[/:action][/:id]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'     => '[0-9]+',
                            ),
                            'defaults' => array(
                                'controller' => 'Admin\Controller\Deadline',
                                'action' => 'index',
                            ),
                        ),
                    ),
                    'agegroup' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route'    => '/agegroup[/:action][/:id]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'     => '[0-9]+',
                            ),
                            'defaults' => array(
                                'controller' => 'Admin\Controller\Agegroup',
                                'action' => 'index',
                            ),
                        ),
                    ),
                    'payment-type' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route'    => '/payment-type[/:action][/:id]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'     => '[0-9]+',
                            ),
                            'defaults' => array(
                                'controller' => 'Admin\Controller\PaymentType',
                                'action' => 'index',
                            ),
                        ),
                    ),
                    'counter' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route'    => '/counter[/:action][/:id]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'     => '[0-9]+',
                            ),
                            'defaults' => array(
                                'controller' => 'Admin\Controller\Counter',
                                'action' => 'index',
                            ),
                        ),
                    ),
                    'user' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route'    => '/user[/:action][/:id]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'     => '[0-9]+',
                            ),
                            'defaults' => array(
                                'controller' => 'Admin\Controller\User',
                                'action' => 'index',
                            ),
                        ),
                    ),
                    'role' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route'    => '/role[/:action][/:id]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'     => '[0-9]+',
                            ),
                            'defaults' => array(
                                'controller' => 'Admin\Controller\Role',
                                'action' => 'index',
                            ),
                        ),
                    ),
                    'order' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route'    => '/order[/:action][/:id]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'     => '[0-9]+',
                            ),
                            'defaults' => array(
                                'controller' => 'Admin\Controller\Order',
                                'action' => 'index',
                            ),
                        ),
                    ),
                    'bankaccount' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route'    => '/bankaccount[/:action][/:id]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'     => '[0-9]+',
                            ),
                            'defaults' => array(
                                'controller' => 'Admin\Controller\Bankaccount',
                                'action' => 'index',
                            ),
                        ),
                    ),
                    'product-price' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route'    => '/product-price[/:action][/:id]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'     => '[0-9]+',
                            ),
                            'defaults' => array(
                                'controller' => 'Admin\Controller\ProductPrice',
                                'action' => 'index',
                            ),
                        ),
                    ),
                    'product-variant' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route'    => '/product-variant[/:action][/:id]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'     => '[0-9]+',
                            ),
                            'defaults' => array(
                                'controller' => 'Admin\Controller\ProductVariant',
                                'action' => 'index',
                            ),
                        ),
                    ),
                    'product-variant-value' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route'    => '/product-variant-value[/:action][/:id]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'     => '[0-9]+',
                            ),
                            'defaults' => array(
                                'controller' => 'Admin\Controller\ProductVariantValue',
                                'action' => 'index',
                            ),
                        ),
                    ),
                    'product-package' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route'    => '/product-package[/:action][/:id][/:subproduct_id]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'     => '[0-9]+',
                                'subproduct_id'     => '[0-9]+',
                            ),
                            'defaults' => array(
                                'controller' => 'Admin\Controller\ProductPackage',
                                'action' => 'index',
                            ),
                        ),
                    ),
                    'country' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route'    => '/country[/:action][/:id]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'     => '[0-9]+',
                            ),
                            'defaults' => array(
                                'controller' => 'Admin\Controller\Country',
                                'action' => 'index',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'admin_main_nav' => 'Admin\Service\AdminNavigationFactory',
            'admin_top_nav' => 'Admin\Service\TopNavigationFactory',
        ),
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ),
        'aliases' => array(
            'translator' => 'MvcTranslator',
        ),
    ),
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'admin' => __DIR__ . '/../view',
        ),
    ),
);
