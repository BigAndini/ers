<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

// module/OnsiteReg/config/module.config.php:
return array(
    'controllers' => array(
        'invokables' => array(
            'OnsiteReg\Controller\Index'       => 'OnsiteReg\Controller\IndexController',
            'OnsiteReg\Controller\Redirect'       => 'OnsiteReg\Controller\RedirectController',
        ),
    ),
    'navigation' => array(
        'onsite_main_nav' => array(
            'home' => array(
                'label' => 'Home',
                'route' => 'home',
                'resource'  => 'controller/OnsiteReg\Controller\Index',
            ),
            'product' => array(
                'label' => 'Products',
                'route' => 'product',
                'resource'  => 'controller/OnsiteReg\Controller\Product',
            ),
            'participant' => array(
                'label' => 'My Persons',
                'route' => 'participant',
                'resource'  => 'controller/OnsiteReg\Controller\Participant',
            ),
            'order' => array(
                'label' => 'My Shopping Cart',
                'route' => 'order',
                'resource'  => 'controller/OnsiteReg\Controller\Order',
            ),
        ),
        'onsite_top_nav' => array(
            'order' => array(
                'label' => 'My Shopping Cart',
                'route' => 'order',
                'resource'  => 'controller/OnsiteReg\Controller\Order',
            ),
            'login' => array(
                'label' => 'Login',
                'route' => 'zfcuser/login',
                #'action' => 'login',
                'resource'  => 'controller/zfcuser:login',
            ),
            /*'register' => array(
                'label' => 'Register',
                'route' => 'zfcuser/register',
                #'action' => 'register',
                'resource'  => 'controller/zfcuser:register',
            ),*/
            'profile' => array(
                'label' => 'My Profile',
                'route' => 'profile',
                'action' => '',
                'resource'  => 'controller/OnsiteReg\Controller\Profile',
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
                'resource'  => 'controller/Admin\Controller\Index',
            ),
            'help' => array(
                /*'label' => '<span class="fa-stack fa-lg">
                        <i class="fa fa-circle fa-stack-2x green"></i>
                        <i class="fa fa-question fa-stack-1x fa-inverse"></i>
                    </span>',*/
                'label' => 'Help',
                'route' => 'info',
                'action' => 'help',
                'resource'  => 'controller/OnsiteReg\Controller\Info',
            ),
        ),
    ),
    'router' => array(
        'routes' => array(
            'onsite' => array(
                'type' => 'segment',
                'options' => array(
                    #'route' => '/admin[/]',
                    'route' => '/onsite',
                    'defaults' => array(
                        'controller' => 'OnsiteReg\Controller\Index',
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
                                'controller' => 'OnsiteReg\Controller\Test',
                                'action'     => 'index',
                            ),
                        ),
                    ),
                    'redirect' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route'    => '/redirect[/:action][/:id]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'     => '[A-Z0-9]+',
                            ),
                            'defaults' => array(
                                'controller' => 'OnsiteReg\Controller\Redirect',
                                'action'     => 'index',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'onsite_main_nav'      => 'OnsiteReg\Service\MainNavigationFactory',
            'onsite_top_nav'       => 'OnsiteReg\Service\TopNavigationFactory',
        ),
        'shared' => array(
            'DOMPDF' => false,
            'ViewPdfRenderer' => false,
            'OnsiteReg\Service\ETicketService' => false,
            'OnsiteReg\Service\AgegroupService:ticket' => false,
        ),
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ),
        'aliases' => array(
            'translator' => 'MvcTranslator',
            'Logger'     => 'EddieJaoude\Zf2Logger',
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
            'onsite' => __DIR__ . '/../view',
        ),
    ),
);