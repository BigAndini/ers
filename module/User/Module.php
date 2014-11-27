<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//  module/RegistrationSystem/Module.php
namespace User;

use User\Model;
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
                'User\Model\UserTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new Model\UserTable($dbAdapter);
                    return $table;
                },
                'User\Model\RoleTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new Model\RoleTable($dbAdapter);
                    return $table;
                },
                /*
                 * Entity Factories
                 */
                'User\Model\Entity\Entity' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $entity = new Model\Entity\Entity($dbAdapter);
                    return $entity;
                },
                'Admin\Model\Entity\User' => function($sm) {
                    $entity = new Model\Entity\User();
                    return $entity;
                },
                'Admin\Model\Entity\Role' => function($sm) {
                    $entity = new Model\Entity\Role();
                    #$entity->setServiceLocator($sm);
                    return $entity;
                },
                /* 
                 * Form Factories
                 */
                'User\Form\UserForm' => function($sm){
                    $form   = new Form\UserForm();
                    
                    return $form;
                },
                'User\Form\LoginForm' => function($sm){
                    $form   = new Form\LoginForm();
                    
                    return $form;
                },
                'User\Form\ForgotPasswordForm' => function($sm){
                    $form   = new Form\ForgotPasswordForm();
                    
                    return $form;
                },
                'User\Form\ResetPasswordForm' => function($sm){
                    $form   = new Form\ResetPasswordForm();
                    
                    return $form;
                },
                'User\Form\RegisterForm' => function($sm){
                    $form   = new Form\RegisterForm();
                    
                    return $form;
                },
            ),
        );
    }
}