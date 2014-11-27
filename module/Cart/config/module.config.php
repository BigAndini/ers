<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

// module/Cart/config/module.config.php:
return array(
    'controllers' => array(
        'invokables' => array(
            'Cart\Controller\ShoppingCart' => 'Cart\Controller\ShoppingCartController',
            'Cart\Controller\Checkout' => 'Cart\Controller\CheckoutController',
        ),
    ),
     'router' => array(
        'routes' => array(
            'shoppingcart' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/shoppingcart[/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Cart\Controller\ShoppingCart',
                        'action' => 'index',
                    ),
                ),
            ),
            'checkout' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/checkout[/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Cart\Controller\Checkout',
                        'action' => 'index',
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'cart' => __DIR__ . '/../view',
        ),
    ),
);