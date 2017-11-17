<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

// module/PreReg/config/module.config.php:
return array(
    'controllers' => [
        'invokables' => [
            'PreReg\Controller\Order'       => 'PreReg\Controller\OrderController',
            'PreReg\Controller\Cart'        => 'PreReg\Controller\CartController',
            'PreReg\Contsroller\Package'     => 'PreReg\Controller\PackageController',
            'PreReg\Controller\Participant' => 'PreReg\Controller\ParticipantController',
            'PreReg\Controller\Buyer'       => 'PreReg\Controller\BuyerController',
            'PreReg\Controller\Product'     => 'PreReg\Controller\ProductController',
            'PreReg\Controller\Profile'     => 'PreReg\Controller\ProfileController',
            'PreReg\Controller\Info'        => 'PreReg\Controller\InfoController',
            'PreReg\Controller\Payment'     => 'PreReg\Controller\PaymentController',
            'PreReg\Controller\Test'        => 'PreReg\Controller\TestController',
        ],
        'factories' => [
            'PreReg\Controller\InfoController' => 'PreReg\Controller\Factory\ControllerFactory',
            'PreReg\Controller\ProfileController' => 'PreReg\Controller\Factory\ControllerFactory',
        ],
    ],
    'navigation' => array(
        'main_nav' => array(
            'home' => array(
                'label' => _('Home'),
                'route' => 'home',
                'resource'  => 'controller/PreReg\Controller\Product',
            ),
            'product' => array(
                'label' => _('Products'),
                'route' => 'product',
                'resource'  => 'controller/PreReg\Controller\Product',
            ),
            'participant' => array(
                'label' => _('Personal Details'),
                'route' => 'participant',
                'resource'  => 'controller/PreReg\Controller\Participant',
            ),
            'order' => array(
                'label' => _('My Shopping Cart'),
                'route' => 'order',
                'resource'  => 'controller/PreReg\Controller\Order',
            ),
        ),
        'top_nav' => array(
            'profile' => array(
                'label' => 'Profile',
                'icon-only-label' => true,
                'icon' => 'fa fa-user',
                'route' => 'admin',
                'pages' => array(
                    'login' => array(
                        'label' => 'Login',
                        'route' => 'zfcuser/login',
                        #'action' => 'login',
                        'resource'  => 'controller/zfcuser:login',
                    ),
                    'register' => array(
                        'label' => 'Organizers Registration',
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
                    'logout' => array(
                        'label' => 'Logout',
                        'route' => 'zfcuser/logout',
                        #'action' => 'logout',
                        'resource'  => 'controller/zfcuser:logout',
                    ),
                ),
            ),
        ),
        'checkout_nav' => array(
            'mycart' => array(
                'label' => _('Shopping Cart'),
                'route' => 'order',
                'action' => 'overview',
                'resource' => 'controller/PreReg\Controller\Order',
            ),
            'buyer' => array(
                'label' => _('Buyer'),
                'route' => 'order',
                'action' => 'buyer',
                'resource' => 'controller/PreReg\Controller\Order',
            ),
            'payment' => array(
                'label' => _('Payment type'),
                'route' => 'order',
                'action' => 'payment',
                'resource' => 'controller/PreReg\Controller\Order',
            ),
            'checkout' => array(
                'label' => _('Checkout'),
                'route' => 'order',
                'action' => 'checkout',
                'resource' => 'controller/PreReg\Controller\Order',
            ),
        ),
        'profile_nav' => array(
            'change-data' => array(
                'label' => _('Change my user data'),
                'route' => 'profile',
                'action' => 'change',
                'resource' => 'controller/PreReg\Controller\Profile',
            ),
            'change-password' => array(
                'label' => _('Change my password'),
                'route' => 'zfcuser/changepassword',
                #'action' => '',
                'resource' => 'controller/PreReg\Controller\Profile',
            ),
            'package' => array(
                'label' => _('View tickets'),
                'route' => 'package',
                #'action' => '',
                'resource' => 'controller/PreReg\Controller\Package',
            ),
            'person' => array(
                'label' => _('My Persons'),
                'route' => 'profile',
                'action' => 'participant',
                'resource' => 'controller/PreReg\Controller\Profile:participant',
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
            'ajax' => array(
                'type' => 'segment',
                'options' => array(
                    'route'    => '/ajax[/:action][/:name]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'name'   => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'PreReg\Controller\Ajax',
                        'action' => 'index',
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
                    'route' => '/info[/:action][/:id]',
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
                    'route' => '/order[/:action][/:hashkey]',
                    'constraints' => array(
                        'action'      => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'hashkey'     => '[A-Z0-9]+',
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
                    'route'    => '/payment[/:action][/:hashkey]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'hashkey'  => '[A-Z0-9]+',
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
                    'route'    => '/product[/][:action][/:product_id][/:item_id]',
                    #'route'    => '/product[/][:action][/:id]',
                    'constraints' => array(
                        'action'            => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'product_id'        => '[0-9]+',
                        'item_id'           => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'PreReg\Controller\Product',
                        'action'     => 'index',
                    ),
                ),
            ),
            'buyer' => array(
                'type' => 'segment',
                'options' => array(
                    'route'    => '/buyer[/][:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'PreReg\Controller\Buyer',
                        'action'     => 'index',
                    ),
                ),
            ),
            'profile' => array(
                'type' => 'segment',
                'options' => array(
                    'route'    => '/profile[/][:action][/:hashkey]',
                    'constraints' => array(
                        'action'   => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'hashkey'  => '[A-Z0-9]+',
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
            'zfcuser' => array(
                // changing to hostname route - using an unreachable hostname
                #'type' => 'Hostname',
                // minimum possible priority - all other routes come first.
                'priority' => ~PHP_INT_MAX,
                /*'options' => array(
                    // foo.bar does not exist - never matched
                    'route' => 'foo.bar',
                    'defaults' => array(
                        'controller' => null,
                        'action' => 'index',
                    ),
                ),*/

                // optional - just if you want to override single child routes:
                'child_routes' => array(
                    'register' => array(
                        'options' => array(
                            'defaults' => array(
                                'controller' => null,
                            ),
                        ),
                    ),
                    
                ),
            ),
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'main_nav'      => 'PreReg\Service\MainNavigationFactory',
            'top_nav'       => 'PreReg\Service\TopNavigationFactory',
            'checkout_nav'  => 'PreReg\Service\CheckoutNavigationFactory',
            'profile_nav'   => 'PreReg\Service\ProfileNavigationFactory',
        ),
        'shared' => array(
            'DOMPDF' => false,
            'ViewPdfRenderer' => false,
            #'PreReg\Service\ETicketService' => false,
            #'PreReg\Service\AgegroupService:ticket' => false,
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
        #'locale' => 'de_DE',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
    'view_manager' => array(
        /*'template_map' => array(
            #'zfc-user/user/login' => __DIR__ . '/../view/zfc-user/user/login.phtml',
            'zfc-user/user/login' => __DIR__ . '/../view/layout/login.phtml',
        ),*/
        'template_path_stack' => array(
            'prereg' => __DIR__ . '/../view',
            #'zfc-user' => __DIR__ . '/../view',
        ),
    ),
    'view_helpers' => array(
        'invokables' => array(
            'formelementerrors' => 'PreReg\Form\View\Helper\FormElementErrors',
            'checkoutactive' => 'PreReg\View\Helper\CheckoutActive',
            'currencychooser' => 'PreReg\View\Helper\CurrencyChooser',
        ),
        'factories' => array(
            'setting' => function($serviceManager) {
                $helper = new \ErsBase\View\Helper\Setting($serviceManager);
                return $helper;
            },
            'config' => function($serviceManager) {
                $helper = new \ErsBase\View\Helper\Config($serviceManager);
                return $helper;
            },
            'session' => function($serviceManager) {
                $helper = new \ErsBase\View\Helper\Session();
                return $helper;
            },
            'checkoutactive' => function($serviceManager) {
                $helper = new \ErsBase\View\Helper\CheckoutActive();
                return $helper;
            },
            'niceiban' => function($serviceManager) {
                $helper = new \ErsBase\View\Helper\NiceIban($serviceManager);
                return $helper;
            },
        ),
    ),
    'session_manager' => [
        /*'config' => [
            'class' => Session\Config\SessionConfig::class,
            'options' => [
                'name' => 'myapp',
            ],
        ],*/
        /*'storage' => Session\Storage\SessionArrayStorage::class,
        'validators' => [
            Session\Validator\RemoteAddr::class,
            Session\Validator\HttpUserAgent::class,
        ],*/
    ],
);
