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
            'Admin\Controller\Admin' => 'Admin\Controller\AdminController',
            'Admin\Controller\Tax' => 'Admin\Controller\TaxController',
            'Admin\Controller\Product' => 'Admin\Controller\ProductController',
            'Admin\Controller\ProductVariant' => 'Admin\Controller\ProductVariantController',
            'Admin\Controller\ProductVariantValue' => 'Admin\Controller\ProductVariantValueController',
            'Admin\Controller\ProductPrice' => 'Admin\Controller\ProductPriceController',
            'Admin\Controller\PriceLimit' => 'Admin\Controller\PriceLimitController',
        ),
    ),
     'router' => array(
        'routes' => array(
            'admin' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/admin',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Admin',
                        'action' => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
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
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'admin' => __DIR__ . '/../view',
        ),
    ),
);