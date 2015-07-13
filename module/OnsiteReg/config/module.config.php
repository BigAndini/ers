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
            'OnsiteReg\Controller\Search'      => 'OnsiteReg\Controller\SearchController',
            'OnsiteReg\Controller\Package'      => 'OnsiteReg\Controller\PackageController',
            'OnsiteReg\Controller\Redirect'    => 'OnsiteReg\Controller\RedirectController',
        ),
    ),
    'navigation' => array(
        'onsite_main_nav' => array(
            'home' => array(
                'label' => 'Home',
                'route' => 'home',
                'resource'  => 'controller/PreReg\Controller\Product',
            ),
        ),
        'onsite_top_nav' => array(
            'login' => array(
                'label' => 'Login',
                'route' => 'zfcuser/login',
                #'action' => 'login',
                'resource'  => 'controller/zfcuser:login',
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
                'resource'  => 'controller/Admin\Controller\Index',
            ),
            'onsite' => array(
                'label' => 'Onsite',
                'route' => 'onsite',
                'resource'  => 'controller/OnsiteReg\Controller\Index',
            ),
        ),
    ),
    'router' => array(
        'routes' => array(
            'onsite' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/onsite',
                    'defaults' => array(
                        'controller' => 'OnsiteReg\Controller\Index',
                        'action' => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'search' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route'    => '/search',
                            'defaults' => array(
                                'controller' => 'OnsiteReg\Controller\Search',
                                'action'     => 'index',
                            ),
                        ),
                    ),
                    'package' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route'    => '/package[/:action][/:id][/:item-id]',
                            'defaults' => array(
                                'controller' => 'OnsiteReg\Controller\Package',
                                'action'     => 'index',
                            ),
                        ),
                    ),
                ),
            ),
            
            'redirect' => array(
                'type' => 'segment',
                'options' => array(
                    'route'    => '/redirect[/:code]',
                    'constraints' => array(
                        'code'     => '[A-Z0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'OnsiteReg\Controller\Redirect',
                        'action'     => 'index',
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