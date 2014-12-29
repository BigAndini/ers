<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

// module/RegistrationSystem/config/module.config.php:
return array(
    'controllers' => array(
        'invokables' => array(
            'Order\Controller\Order' => 'Order\Controller\OrderController',
            'Order\Controller\Cart' => 'Order\Controller\CartController',
            'Order\Controller\Package' => 'Order\Controller\PackageController',
        ),
    ),
     'router' => array(
        'routes' => array(
            'cart' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/cart[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Order\Controller\Cart',
                        'action' => 'index',
                    ),
                ),
            ),
            'order' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/order',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Order\Controller\Order',
                        'action' => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'participant' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route'    => '/participant[/:action][/:id]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'     => '[0-9]+',
                            ),
                            'defaults' => array(
                                'controller' => 'Order\Controller\Participant',
                                'action'     => 'index',
                            ),
                        ),
                    ),
                    /*'product' => array(
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
                    'price-limit' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route'    => '/price-limit[/:action][/:id]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'     => '[0-9]+',
                            ),
                            'defaults' => array(
                                'controller' => 'Admin\Controller\PriceLimit',
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
                    ),*/
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'order' => __DIR__ . '/../view',
        ),
    ),
);