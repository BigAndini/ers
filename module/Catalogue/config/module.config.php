<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

// module/Catalogue/config/module.config.php:
return array(
    'controllers' => array(
        'invokables' => array(
            'Catalogue\Controller\StickyNotes' => 'Catalogue\Controller\StickyNotesController',
            'Catalogue\Controller\User' => 'Catalogue\Controller\UserController',
            'Catalogue\Controller\ShoppingCart' => 'Catalogue\Controller\ShoppingCartController',
            'Catalogue\Controller\Product' => 'Catalogue\Controller\ProductController',
        ),
    ),
     'router' => array(
        'routes' => array(
            'product' => array(
                'type' => 'segment',
                'options' => array(
                    'route'    => '/product[/][:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Catalogue\Controller\Product',
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'catalogue' => __DIR__ . '/../view',
        ),
    ),
);