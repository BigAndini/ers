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
            'PreReg\Controller\Product'     => 'PreReg\Controller\ProductController',
            'PreReg\Controller\Profile'     => 'PreReg\Controller\ProfileController',
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
                'label' => 'My Participants',
                'route' => 'participant',
                'resource'  => 'controller/PreReg\Controller\Participant',
            ),
            'cart-reset' => array(
                'label' => 'Reset Shopping Cart',
                'route' => 'cart',
                'action' => 'reset',
                'resource'  => 'controller/PreReg\Controller\Cart:reset',
            ),
        ),
        'top_nav' => array(
            'order' => array(
                'label' => 'My Shopping Cart',
                'route' => 'order',
                'resource'  => 'controller/PreReg\Controller\Order:index',
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
        ),
    ),
    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        'controller' => 'PreReg\Controller\Product',
                        'action'     => 'index',
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
            'cart' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/cart[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
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
                    'route'    => '/participant[/:action][/:id]',
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
                    'route'    => '/product[/][:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'PreReg\Controller\Product',
                        'action'     => 'index',
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