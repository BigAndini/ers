<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

return array(
    'controllers' => array(
        'invokables' => array(
            'Admin\Controller\Index'                => 'Admin\Controller\IndexController',
            'Admin\Controller\Statistic'            => 'Admin\Controller\StatisticController',
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
            'Admin\Controller\Package'              => 'Admin\Controller\PackageController',
            'Admin\Controller\Item'                 => 'Admin\Controller\ItemController',
            'Admin\Controller\Bankaccount'          => 'Admin\Controller\BankaccountController',
            'Admin\Controller\Country'              => 'Admin\Controller\CountryController',
            'Admin\Controller\Cron'                 => 'Admin\Controller\CronController',
            'Admin\Controller\Matching'             => 'Admin\Controller\MatchingController',
            'Admin\Controller\Refund'               => 'Admin\Controller\RefundController',
            'Admin\Controller\Ajax'                 => 'Admin\Controller\AjaxController',
            'Admin\Controller\Status'               => 'Admin\Controller\StatusController',
            'Admin\Controller\Test'                 => 'Admin\Controller\TestController',
            'Admin\Controller\Overview'             => 'Admin\Controller\OverviewController',
            'Admin\Controller\Currency'             => 'Admin\Controller\CurrencyController',
        ),
    ),
    'navigation' => array(
        'admin_main_nav' => array(
            /*'home' => array(
                'label' => 'Frontend',
                'route' => 'home',
                'target' => '_blank',
                'resource'  => 'controller/PreReg\Controller\Product',
            ),*/
            'statistic' => array(
                'label' => 'Stats',
                'icon' => 'fa fa-bar-chart',
                'route' => 'admin/statistic',
                'pages' => array(
                    /*'orgas' => array(
                        'label' => 'for Orgas',
                        'route' => 'admin/statistic',
                        'action' => 'orgas',
                        'resource'  => 'controller/Admin\Controller\Statistic',
                    ),*/
                    'order' => array(
                        'label' => 'Orders',
                        'route' => 'admin/statistic',
                        'action' => 'orders',
                        'resource'  => 'controller/Admin\Controller\Statistic',
                    ),
                    'participant' => array(
                        'label' => 'Participants',
                        'route' => 'admin/statistic',
                        'action' => 'participants',
                        'resource'  => 'controller/Admin\Controller\Statistic',
                    ),
                    'account' => array(
                        'label' => 'Bank accounts',
                        'route' => 'admin/statistic',
                        'action' => 'paymenttypes',
                        'resource'  => 'controller/Admin\Controller\Statistic',
                    ),
                    'onsite' => array(
                        'label' => 'Onsite',
                        'route' => 'admin/statistic',
                        'action' => 'onsite',
                        'resource'  => 'controller/Admin\Controller\Statistic',
                    ),
                ),
            ),
            'shop' => array(
                'label' => 'Shop',
                'icon' => 'fa fa-shopping-cart',
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
                    'currency' => array(
                        'label' => 'Currency',
                        'route' => 'admin/currency',
                        'resource'  => 'controller/Admin\Controller\Currency',
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
                    'counter' => array(
                        'label' => 'Counter',
                        'route' => 'admin/counter',
                        'resource'  => 'controller/Admin\Controller\Counter',
                    ),
                    'status' => array(
                        'label' => 'Status',
                        'route' => 'admin/status',
                        'resource'  => 'controller/Admin\Controller\Status',
                    ),
                ),
            ),
            'product' => array(
                'label' => 'Product',
                'route' => 'admin/product',
                #'action' => 'reset',
                'resource'  => 'controller/Admin\Controller\Product',
            ),
            'Overview' => array(
                'label' => 'Overview',
                'route' => 'admin/overview',
                #'action' => 'reset',
                'resource'  => 'controller/Admin\Controller\Overview',
                'pages' => array(
                    'config' => array(
                        'label' => 'Config',
                        'route' => 'admin/overview',
                        'action' => 'config',
                        'resource'  => 'controller/Admin\Controller\Overview',
                    ),
                ),
            ),
            'user' => array(
                'label' => 'User',
                'icon' => 'fa fa-users',
                'route' => 'admin/user',
                'resource'  => 'controller/Admin\Controller\User',
                'pages' => array(
                    'user' => array(
                        'label' => 'User',
                        'route' => 'admin/user',
                        'resource'  => 'controller/Admin\Controller\User',
                    ),
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
                #'resource'  => 'controller/Admin\Controller\Order',
                'pages' => array(
                    /*'overview' => array(
                        'label' => 'Overview',
                        'route' => 'admin/order',
                        'resource'  => 'controller/Admin\Controller\Order',
                    ),*/
                    'search' => array(
                        'label' => 'Search',
                        'route' => 'admin/order',
                        'action' => 'search',
                        'resource'  => 'controller/Admin\Controller\Order',
                    ),
                    'zero-euro-tickets' => array(
                        'label' => '0€-Week-Tickets',
                        'route' => 'admin/order',
                        'action' => 'zero-euro-tickets',
                        'resource'  => 'controller/Admin\Controller\Order',
                    ),
                    'overpaid-orders' => array(
                        'label' => 'Overpaid Orders',
                        'route' => 'admin/order',
                        'action' => 'overpaid-orders',
                        'resource'  => 'controller/Admin\Controller\Order',
                    ),
                ),
            ),
            'matching' => array(
                'label' => 'Matching',
                'route' => 'admin',
                'pages' => array(
                    'overview' => array(
                        'label' => 'Overview',
                        'route' => 'admin/matching',
                        'action' => 'index',
                        'resource'  => 'controller/Admin\Controller\Matching',
                    ),
                    'bankaccount' => array(
                        'label' => 'Bank accounts',
                        'route' => 'admin/bankaccount',
                        'resource'  => 'controller/Admin\Controller\Bankaccount',
                    ),
                    'upload-csv' => array(
                        'label' => 'Upload CSV',
                        'route' => 'admin/bankaccount',
                        'action' => 'upload-csv',
                        'resource'  => 'controller/Admin\Controller\Bankaccount',
                    ),
                    'manual' => array(
                        'label' => 'Manual Matching',
                        'route' => 'admin/matching',
                        'action' => 'manual',
                        'resource'  => 'controller/Admin\Controller\Matching',
                    ),
                    'disabled' => array(
                        'label' => 'Disabled Statements',
                        'route' => 'admin/matching',
                        'action' => 'disabled',
                        'resource'  => 'controller/Admin\Controller\Matching',
                    ),
                ),
            ),
            'refund' => array(
                'label' => 'Refund',
                'route' => 'admin',
                'pages' => array(
                    'overview' => array(
                        'label' => 'Refund pending',
                        'route' => 'admin/refund',
                        'action' => 'index',
                        'resource'  => 'controller/Admin\Controller\Refund',
                    ),
                ),
            ),
        ),
        'admin_top_nav' => array(
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
    ),
    'console' => array(
        'router' => array(
            'routes' => array(
                'cron' => array(
                    'options' => array(
                        'route'    => 'cron',
                        'defaults' => array(
                            'controller' => 'Admin\Controller\Cron',
                            'action' => 'cron'
                        )
                    )
                ),
                'update-orders' => array(
                    'options' => array(
                        'route'    => 'update-orders',
                        'defaults' => array(
                            'controller' => 'Admin\Controller\Cron',
                            'action' => 'update-orders'
                        )
                    )
                ),
                'auto-matching' => array(
                    'options' => array(
                        'route'    => 'auto-matching',
                        'defaults' => array(
                            'controller' => 'Admin\Controller\Cron',
                            'action' => 'auto-matching'
                        )
                    )
                ),
                'overpaid-orders' => array(
                    'options' => array(
                        'route'    => 'overpaid-orders',
                        'defaults' => array(
                            'controller' => 'Admin\Controller\Cron',
                            'action' => 'overpaid-orders'
                        )
                    )
                ),
                'clean-pending-orders' => array(
                    'options' => array(
                        'route'    => 'clean-pending-orders',
                        'defaults' => array(
                            'controller' => 'Admin\Controller\Cron',
                            'action' => 'clean-pending-orders'
                        )
                    )
                ),
                'remove-matches' => array(
                    'options' => array(
                        'route'    => 'remove-matches',
                        'defaults' => array(
                            'controller' => 'Admin\Controller\Cron',
                            'action' => 'remove-matches'
                        )
                    )
                ),
                'generate-etickets' => array(
                    'options' => array(
                        'route'    => 'generate-etickets',
                        'defaults' => array(
                            'controller' => 'Admin\Controller\Cron',
                            'action' => 'generate-etickets'
                        )
                    )
                ),
                'send-payment-reminder' => array(
                    'options' => array(
                        'route'    => 'send-payment-reminder [--real|-r]',
                        'defaults' => array(
                            'controller' => 'Admin\Controller\Cron',
                            'action' => 'send-payment-reminder'
                        )
                    )
                ),
                'send-etickets' => array(
                    'options' => array(
                        'route'    => 'send-etickets [--count=|-c=] [--real|-r] [--debug|-d]',
                        'defaults' => array(
                            'controller' => 'Admin\Controller\Cron',
                            'action' => 'send-etickets'
                        )
                    )
                ),
                'send-u-etickets' => array(
                    'options' => array(
                        'route'    => 'send-u-etickets',
                        'defaults' => array(
                            'controller' => 'Admin\Controller\Cron',
                            'action' => 'send-u-etickets'
                        )
                    )
                ),
                'email-status' => array(
                    'options' => array(
                        'route'    => 'email-status',
                        'defaults' => array(
                            'controller' => 'Admin\Controller\Cron',
                            'action' => 'email-status'
                        )
                    )
                ),
                'item-agegroup' => array(
                    'options' => array(
                        'route'    => 'item-agegroup',
                        'defaults' => array(
                            'controller' => 'Admin\Controller\Cron',
                            'action' => 'item-agegroup'
                        )
                    )
                ),
                'correct-item-status' => array(
                    'options' => array(
                        'route'    => 'correct-item-status',
                        'defaults' => array(
                            'controller' => 'Admin\Controller\Cron',
                            'action' => 'correct-item-status'
                        )
                    )
                ),
                'gen-user-list' => array(
                    'options' => array(
                        'route'    => 'gen-user-list',
                        'defaults' => array(
                            'controller' => 'Admin\Controller\Cron',
                            'action' => 'gen-user-list'
                        )
                    )
                ),
                'calc-sums' => array(
                    'options' => array(
                        'route'    => 'calc-sums',
                        'defaults' => array(
                            'controller' => 'Admin\Controller\Cron',
                            'action' => 'calc-sums'
                        )
                    )
                ),
                'cleanup-user' => array(
                    'options' => array(
                        'route'    => 'cleanup-user',
                        'defaults' => array(
                            'controller' => 'Admin\Controller\Cron',
                            'action' => 'cleanup-user'
                        )
                    )
                ),
                'cleanup-order' => array(
                    'options' => array(
                        'route'    => 'cleanup-order',
                        'defaults' => array(
                            'controller' => 'Admin\Controller\Cron',
                            'action' => 'cleanup-order'
                        )
                    )
                ),
                'correct-buyer-role' => array(
                    'options' => array(
                        'route'    => 'correct-buyer-role',
                        'defaults' => array(
                            'controller' => 'Admin\Controller\Cron',
                            'action' => 'correct-buyer-role'
                        )
                    )
                ),
                'correct-status' => array(
                    'options' => array(
                        'route'    => 'correct-status',
                        'defaults' => array(
                            'controller' => 'Admin\Controller\Cron',
                            'action' => 'correct-status'
                        )
                    )
                ),
                'correct-package-status' => array(
                    'options' => array(
                        'route'    => 'correct-package-status',
                        'defaults' => array(
                            'controller' => 'Admin\Controller\Cron',
                            'action' => 'correct-package-status'
                        )
                    )
                ),
                'correct-packages-in-paid-orders' => array(
                    'options' => array(
                        'route'    => 'correct-packages-in-paid-orders',
                        'defaults' => array(
                            'controller' => 'Admin\Controller\Cron',
                            'action' => 'correct-packages-in-paid-orders'
                        )
                    )
                ),
                'correct-active-user' => array(
                    'options' => array(
                        'route'    => 'correct-active-user',
                        'defaults' => array(
                            'controller' => 'Admin\Controller\Cron',
                            'action' => 'correct-active-user'
                        )
                    )
                ),
                'correct-paid-orders' => array(
                    'options' => array(
                        'route'    => 'correct-paid-orders',
                        'defaults' => array(
                            'controller' => 'Admin\Controller\Cron',
                            'action' => 'correct-paid-orders'
                        )
                    )
                ),
                'correct-ordered-orders' => array(
                    'options' => array(
                        'route'    => 'correct-ordered-orders',
                        'defaults' => array(
                            'controller' => 'Admin\Controller\Cron',
                            'action' => 'correct-ordered-orders'
                        )
                    )
                ),
                'correct-paid-packages' => array(
                    'options' => array(
                        'route'    => 'correct-paid-packages',
                        'defaults' => array(
                            'controller' => 'Admin\Controller\Cron',
                            'action' => 'correct-paid-packages'
                        )
                    )
                ),
                'correct-ordered-packages' => array(
                    'options' => array(
                        'route'    => 'correct-ordered-packages',
                        'defaults' => array(
                            'controller' => 'Admin\Controller\Cron',
                            'action' => 'correct-ordered-packages'
                        )
                    )
                ),
                'correct-item-status' => array(
                    'options' => array(
                        'route'    => 'correct-item-status',
                        'defaults' => array(
                            'controller' => 'Admin\Controller\Cron',
                            'action' => 'correct-item-status'
                        )
                    )
                ),
                'sorry-eticket-sepa' => array(
                    'options' => array(
                        'route'    => 'sorry-eticket-sepa',
                        'defaults' => array(
                            'controller' => 'Admin\Controller\Cron',
                            'action' => 'sorry-eticket-sepa'
                        )
                    )
                ),
                'sorry-eticket-cc' => array(
                    'options' => array(
                        'route'    => 'sorry-eticket-cc',
                        'defaults' => array(
                            'controller' => 'Admin\Controller\Cron',
                            'action' => 'sorry-eticket-cc'
                        )
                    )
                ),
            ),
        ),
    ),
    'router' => array(
        'routes' => array(
            'admin' => array(
                'type' => 'segment',
                'priority' => 10,
                'options' => array(
                    #'route' => '/admin[/]',
                    'route' => '/admin',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Index',
                        'action' => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'test' => array(
                        'type' => 'segment',
                        'priority' => 10,
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
                    'statistic' => array(
                        'type' => 'segment',
                        'priority' => 10,
                        'options' => array(
                            'route'    => '/statistic[/:action]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                            ),
                            'defaults' => array(
                                'controller' => 'Admin\Controller\Statistic',
                                'action' => 'index'
                            ),
                        ),
                    ),
                    'tax' => array(
                        'type' => 'segment',
                        'priority' => 10,
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
                        'priority' => 10,
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
                    'matching' => array(
                        'type' => 'segment',
                        'priority' => 10,
                        'options' => array(
                            'route'    => '/matching[/:action][/:id]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'     => '[0-9]+',
                            ),
                            'defaults' => array(
                                'controller' => 'Admin\Controller\Matching',
                                'action' => 'index',
                            ),
                        ),
                    ),
                    'refund' => array(
                        'type' => 'segment',
                        'priority' => 10,
                        'options' => array(
                            'route'    => '/refund[/:action][/:id]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'     => '[0-9]+',
                            ),
                            'defaults' => array(
                                'controller' => 'Admin\Controller\Refund',
                                'action' => 'index',
                            ),
                        ),
                    ),
                    'deadline' => array(
                        'type' => 'segment',
                        'priority' => 10,
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
                        'priority' => 10,
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
                    'currency' => array(
                        'type' => 'segment',
                        'priority' => 10,
                        'options' => array(
                            'route'    => '/currency[/:action][/:id]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'     => '[0-9]+',
                            ),
                            'defaults' => array(
                                'controller' => 'Admin\Controller\Currency',
                                'action' => 'index',
                            ),
                        ),
                    ),
                    'payment-type' => array(
                        'type' => 'segment',
                        'priority' => 10,
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
                    'overview' => array(
                        'type' => 'segment',
                        'priority' => 10,
                        'options' => array(
                            'route'    => '/overview[/:action][/:id]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'     => '[0-9]+',
                            ),
                            'defaults' => array(
                                'controller' => 'Admin\Controller\Overview',
                                'action' => 'index',
                            ),
                        ),
                    ),
                    'ajax' => array(
                        'type' => 'segment',
                        'priority' => 10,
                        'options' => array(
                            'route'    => '/ajax[/:action][/:id]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'     => '[0-9]+',
                            ),
                            'defaults' => array(
                                'controller' => 'Admin\Controller\Ajax',
                                'action' => 'index',
                            ),
                        ),
                    ),
                    'counter' => array(
                        'type' => 'segment',
                        'priority' => 10,
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
                    'status' => array(
                        'type' => 'segment',
                        'priority' => 10,
                        'options' => array(
                            'route'    => '/status[/:action][/:id]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'     => '[0-9]+',
                            ),
                            'defaults' => array(
                                'controller' => 'Admin\Controller\Status',
                                'action' => 'index',
                            ),
                        ),
                    ),
                    'user' => array(
                        'type' => 'segment',
                        'priority' => 10,
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
                        'priority' => 10,
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
                        'priority' => 10,
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
                    'package' => array(
                        'type' => 'segment',
                        'priority' => 10,
                        'options' => array(
                            'route'    => '/package[/:action][/:id]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'     => '[0-9]+',
                            ),
                            'defaults' => array(
                                'controller' => 'Admin\Controller\Package',
                                'action' => 'index',
                            ),
                        ),
                    ),
                    'item' => array(
                        'type' => 'segment',
                        'priority' => 10,
                        'options' => array(
                            'route'    => '/item[/:action][/:id]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'     => '[0-9]+',
                            ),
                            'defaults' => array(
                                'controller' => 'Admin\Controller\Item',
                                'action' => 'index',
                            ),
                        ),
                    ),
                    'bankaccount' => array(
                        'type' => 'segment',
                        'priority' => 10,
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
                        'priority' => 10,
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
                        'priority' => 10,
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
                        'priority' => 10,
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
                        'priority' => 10,
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
                        'priority' => 10,
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
            #'Logger'     => 'EddieJaoude\Zf2Logger',
            #'Logger'     => 'Zend\Log\Logger',
        ),
    ),
    'translator' => array(
        #'locale' => 'en_US',
        'locale' => 'de_DE',
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
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),
);
