<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//  module/RegistrationSystem/Module.php
namespace Admin;

use Admin\Model;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\ModuleRouteListener;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $eventManager->getSharedManager()->attach('Zend\Mvc\Controller\AbstractActionController', 'dispatch', function($e) {
            $controller      = $e->getTarget();
            $controllerClass = get_class($controller);
            $moduleNamespace = substr($controllerClass, 0, strpos($controllerClass, '\\'));
            $config          = $e->getApplication()->getServiceManager()->get('config');
            if (isset($config['module_layouts'][$moduleNamespace])) {
                $controller->layout($config['module_layouts'][$moduleNamespace]);
            }
        }, 100);
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
    }
    
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
    
    public function getServiceConfig() {
        return array(
            'factories' => array(
                /* 
                 * MySQL Table Factories
                 */
                'Admin\Model\UserTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new Model\UserTable($dbAdapter);
                    return $table;
                },
                'Admin\Model\RoleTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new Model\RoleTable($dbAdapter);
                    return $table;
                },
                'Admin\Model\TaxTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new Model\TaxTable($dbAdapter);
                    return $table;
                },
                'Admin\Model\ProductGroupTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new Model\ProductGroupTable($dbAdapter);
                    return $table;
                },
                'Admin\Model\ProductVariantTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new Model\ProductVariantTable($dbAdapter);
                    return $table;
                },
                'Admin\Model\ProductVariantValueTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new Model\ProductVariantValueTable($dbAdapter);
                    return $table;
                },
                'Admin\Model\ProductTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new Model\ProductTable($dbAdapter);
                    return $table;
                },
                'Admin\Model\ProductPriceTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new Model\ProductPriceTable($dbAdapter);
                    return $table;
                },
                'Admin\Model\PriceLimitTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new Model\PriceLimitTable($dbAdapter);
                    return $table;
                },
                /*
                 * Entity Factories
                 */
                'Admin\Model\Entity\Entity' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $entity = new Model\Entity\Entity($dbAdapter);
                    return $entity;
                },
                'Admin\Model\Entity\Tax' => function($sm) {
                    $entity = new Model\Entity\Tax();
                    #$entity->setServiceLocator($sm);
                    return $entity;
                },
                'Admin\Model\Entity\Product' => function($sm) {
                    $entity = new Model\Entity\Product();
                    $entity->setServiceLocator($sm);
                    return $entity;
                },
                'Admin\Model\Entity\ProductPrice' => function($sm) {
                    $entity = new Model\Entity\ProductPrice();
                    #$entity->setServiceLocator($sm);
                    return $entity;
                },
                'Admin\Model\Entity\ProductVariant' => function($sm) {
                    $entity = new Model\Entity\ProductVariant();
                    $entity->setServiceLocator($sm);
                    return $entity;
                },
                'Admin\Model\Entity\ProductVariantValue' => function($sm) {
                    $entity = new Model\Entity\ProductVariantValue();
                    $entity->setServiceLocator($sm);
                    return $entity;
                },
                'Admin\Model\Entity\PriceLimit' => function($sm) {
                    $entity = new Model\Entity\PriceLimit();
                    $entity->setServiceLocator($sm);
                    return $entity;
                },
                /* 
                 * Form Factories
                 */
                'Admin\Form\ProductForm' => function($sm){
                    $form   = new Form\ProductForm();
                    
                    /*$ProductGroupTable = $sm->get('Admin\Model\ProductGroupTable');
                    $productgroups = $ProductGroupTable->fetchAll();
                    $options = array();
                    foreach($productgroups as $group) {
                        $options[$group->id] = $group->name;
                    }

                    $form->get('ProductGroup_id')->setValueOptions($options);*/
                    
                    $TaxTable = $sm->get('Admin\Model\TaxTable');
                    $taxes = $TaxTable->fetchAll();
                    $options = array();
                    foreach($taxes as $tax) {
                        $options[$tax->id] = $tax->name.' - '.$tax->percentage.'%';
                    }

                    $form->get('Tax_id')->setValueOptions($options);
                    
                    return $form;
                },
                'Admin\Form\ProductVariantForm' => function($sm){
                    $form   = new Form\ProductVariantForm();
                    
                    $options = array();
                    $options['text'] = 'Text';
                    $options['date'] = 'Date';
                    $options['select'] = 'Select';

                    $form->get('type')->setValueOptions($options);
                    
                    return $form;
                },
            ),
        );
    }
}