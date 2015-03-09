<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

// module/PreReg/config/module.config.php:
return array(
    'controllers' => array(
        'invokables' => array(
            'PreReg\Controller\Order'       => 'PreReg\Controller\OrderController',
            'PreReg\Controller\Cart'        => 'PreReg\Controller\CartController',
            'PreReg\Controller\Package'     => 'PreReg\Controller\PackageController',
            'PreReg\Controller\Participant' => 'PreReg\Controller\ParticipantController',
            'PreReg\Controller\Purchaser'   => 'PreReg\Controller\PurchaserController',
            'PreReg\Controller\Product'     => 'PreReg\Controller\ProductController',
            'PreReg\Controller\Profile'     => 'PreReg\Controller\ProfileController',
            'PreReg\Controller\Info'        => 'PreReg\Controller\InfoController',
            'PreReg\Controller\Payment'     => 'PreReg\Controller\PaymentController',
            'PreReg\Controller\Test'        => 'PreReg\Controller\TestController',
        ),
    ),
    'navigation' => array(
        'main_nav' => array(
            'home' => array(
                'label' => 'Home',
                'route' => 'home',
                'resource'  => 'controller/PreReg\Controller\Product',
            ),
            'product' => array(
                'label' => 'Products',
                'route' => 'product',
                'resource'  => 'controller/PreReg\Controller\Product',
            ),
            'participant' => array(
                'label' => 'My Persons',
                'route' => 'participant',
                'resource'  => 'controller/PreReg\Controller\Participant',
            ),
            'order' => array(
                'label' => 'My Shopping Cart',
                'route' => 'order',
                'resource'  => 'controller/PreReg\Controller\Order',
            ),
            /*'cart-reset' => array(
                'label' => 'Reset Shopping Cart',
                'route' => 'cart',
                'action' => 'reset',
                'resource'  => 'controller/PreReg\Controller\Cart:reset',
            ),*/
        ),
        'top_nav' => array(
            'order' => array(
                'label' => 'My Shopping Cart',
                'route' => 'order',
                'resource'  => 'controller/PreReg\Controller\Order',
            ),
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
                'route' => 'profile',
                'action' => '',
                'resource'  => 'controller/PreReg\Controller\Profile',
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
        'checkout_nav' => array(
            'mycart' => array(
                'label' => 'Shopping Cart',
                'route' => 'order',
                'action' => 'overview',
                'resource' => 'controller/PreReg\Controller\Order',
            ),
            'purchaser' => array(
                'label' => 'Purchaser',
                'route' => 'order',
                'action' => 'purchaser',
                'resource' => 'controller/PreReg\Controller\Order',
            ),
            'payment' => array(
                'label' => 'Payment type    ',
                'route' => 'order',
                'action' => 'payment',
                'resource' => 'controller/PreReg\Controller\Order',
            ),
            'checkout' => array(
                'label' => 'Checkout',
                'route' => 'order',
                'action' => 'checkout',
                'resource' => 'controller/PreReg\Controller\Order',
            ),
        ),
    ),
    'router' => array(
        'routes' => array(
            'test' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/test[/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'PreReg\Controller\Test',
                        'action' => 'index',
                    ),
                ),
            ),
            'maintenance' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        'controller' => 'PreReg\Controller\Maintenance',
                        'action'     => 'index',
                    ),
                ),
            ),
            'home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        'controller' => 'PreReg\Controller\Info',
                        'action'     => 'index',
                    ),
                ),
            ),
            'info' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/info[/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'PreReg\Controller\Info',
                        'action' => 'index',
                    ),
                ),
            ),
            'order' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/order[/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'PreReg\Controller\Order',
                        'action' => 'index',
                    ),
                ),
            ),
            'payment' => array(
                'type' => 'segment',
                'options' => array(
                    'route'    => '/payment[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'PreReg\Controller\Payment',
                        'action'     => 'index',
                    ),
                ),
            ),
            'cart' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/cart[/:action][/:participant_id/:item_id]',
                    'constraints' => array(
                        'action'             => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'participant_id'     => '[0-9]+',
                        'item_id'            => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'PreReg\Controller\Cart',
                        'action' => 'index',
                    ),
                ),
            ),
            'participant' => array(
                'type' => 'segment',
                'options' => array(
                    'route'    => '/participant[/][:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'PreReg\Controller\Participant',
                        'action'     => 'index',
                    ),
                ),
            ),
            'product' => array(
                'type' => 'segment',
                'options' => array(
                    #'route'    => '/product[/][:action][/:product_id][/:participant_id][/:item_id][/:variants]',
                    'route'    => '/product[/][:action][/:product_id][/:participant_id][/:item_id]',
                    'constraints' => array(
                        'action'            => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'product_id'        => '[0-9]+',
                        'participant_id'    => '[0-9]+',
                        'item_id'           => '[0-9]+',
                        'variants'          => '%[a-zA-Z0-9%]*',
                    ),
                    'defaults' => array(
                        'controller' => 'PreReg\Controller\Product',
                        'action'     => 'index',
                    ),
                ),
            ),
            'purchaser' => array(
                'type' => 'segment',
                'options' => array(
                    'route'    => '/purchaser[/][:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'PreReg\Controller\Purchaser',
                        'action'     => 'index',
                    ),
                ),
            ),
            'profile' => array(
                'type' => 'segment',
                'options' => array(
                    'route'    => '/profile[/][:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'PreReg\Controller\Profile',
                        'action'     => 'index',
                    ),
                ),
            ),
            'package' => array(
                'type' => 'segment',
                'options' => array(
                    'route'    => '/package[/][:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'PreReg\Controller\Package',
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'main_nav' => 'PreReg\Service\MainNavigationFactory',
            'top_nav' => 'PreReg\Service\TopNavigationFactory',
            'checkout_nav' => 'PreReg\Service\CheckoutNavigationFactory',
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
            'prereg' => __DIR__ . '/../view',
        ),
    ),
);