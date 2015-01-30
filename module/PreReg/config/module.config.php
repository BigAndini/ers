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
            'PreReg\Controller\Order' => 'PreReg\Controller\OrderController',
            'PreReg\Controller\Cart' => 'PreReg\Controller\CartController',
            'PreReg\Controller\Package' => 'PreReg\Controller\PackageController',
            'PreReg\Controller\Participant' => 'PreReg\Controller\ParticipantController',
            'PreReg\Controller\Product' => 'PreReg\Controller\ProductController',
        ),
    ),
    'navigation' => array(
        'default' => array(
            'home' => array(
                'label' => 'Home',
                'route' => 'home',
                #'resource'   => 'PreReg\Controller\Product:index',
            ),
            'product' => array(
                'label' => 'Products',
                'route' => 'product',
            ),
            'participant' => array(
                'label' => 'My Participants',
                'route' => 'participant',
            ),
            'cart-reset' => array(
                'label' => 'Reset Shopping Cart',
                'route' => 'cart',
                'action' => 'reset',
                'resource'   => 'PreReg\Controller\Cart:reset',
            ),
        ),
        'topnav' => array(
            'default' => array(
                'order' => array(
                    'label' => 'My Shopping Cart',
                    'route' => 'order',
                ),
                'login' => array(
                    'label' => 'Login',
                    'route' => 'zfcuser',
                ),
                'register' => array(
                    'label' => 'Register',
                    'route' => 'zfcuser',
                    'action' => 'register',
                ),
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
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'main_nav' => 'Zend\Navigation\Service\DefaultNavigationFactory',
            'top_nav' => 'Zend\Navigation\Service\DefaultNavigationFactory',
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