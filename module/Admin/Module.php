<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//  module/RegistrationSystem/Module.php
namespace Admin;

#use Admin\Model;
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
                'doctrine.entitymanager'  => new \DoctrineORMModule\Service\EntityManagerFactory('orm_default'),
                /* 
                 * Form Factories
                 */
                'Admin\Form\ProductForm' => function($sm){
                    $form   = new Form\ProductForm();
                    
                    $em = $sm->get('doctrine.entitymanager');
                    $taxes = $em->getRepository("ersEntity\Entity\Tax")->findAll();
                    
                    $options = array();
                    foreach($taxes as $tax) {
                        $options[$tax->getId()] = $tax->getName().' - '.$tax->getPercentage().'%';
                    }

                    $form->get('taxId')->setValueOptions($options);
                    
                    return $form;
                },
                'Admin\Form\RoleForm' => function($sm){
                    $form = new Form\RoleForm();
                    
                    $em = $sm->get('doctrine.entitymanager');
                    $roles = $em->getRepository("ersEntity\Entity\Role")->findAll();
                    
                    $options = array();
                    $options[null] = '';
                    foreach($roles as $role) {
                        $options[$role->getId()] = $role->getRoleId();
                    }

                    $form->get('parent_id')->setValueOptions($options);
                    
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