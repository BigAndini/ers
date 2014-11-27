<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//  module/Cart/Module.php
namespace Cart;

use Cart\Model;
use Zend\Mvc\ModuleRouteListener;
use Zend\Session\SessionManager;
use Zend\Session\Container;

class Module
{
    
    public function onBootstrap($e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        #$serviceManager      = $e->getApplication()->getServiceManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        $this->bootstrapSession($e);
    }

    public function bootstrapSession($e)
    {
        $session = $e->getApplication()
                     ->getServiceManager()
                     ->get('Zend\Session\SessionManager');
        $session->start();
        
        /*foreach($_SESSION as $key => $value) {
            error_log('Session key: '.$key.':');
            if(is_array($value)) {
                foreach($value as $k => $v) {
                    error_log('value: '.$k.' = '.$v);
                }
            } elseif(is_object($value)) {
                error_log('Class of object: '.get_class($value));
                if(get_class($value) == 'Zend\Stdlib\ArrayObject') {
                    foreach($value as $k => $v) {
                        error_log('value: '.$k.' = '.$v);
                    }
                }
                
            }
        }*/
        if($session->isValid()) {
            error_log('Session is valid');
        } else {
            error_log('Session is not valid');
        }

        $container = new Container('initialized');
        if (!isset($container->init)) {
             $session->regenerateId(true);
             $container->init = 1;
        }
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
                 * MySQL Factories
                 */
                'Cart\Model\StickyNotesTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new Model\StickyNotesTable($dbAdapter);
                    return $table;
                },
                'Cart\Model\UserTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new Model\UserTable($dbAdapter);
                    return $table;
                },
                'Cart\Model\RoleTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new Model\RoleTable($dbAdapter);
                    return $table;
                },
                'Cart\Model\ProductTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new Model\ProductTable($dbAdapter);
                    return $table;
                },
                'Cart\Model\ProductVariantTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new Model\ProductVariantTable($dbAdapter);
                    return $table;
                },
                'Cart\Model\ProductVariantValueTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new Model\ProductVariantValueTable($dbAdapter);
                    return $table;
                },
                /* 
                 * Form Factories
                 */
                'Cart\Form\ProductViewForm' => function($sm){
                    $form   = new Form\ProductViewForm();
                    
                    /*$TaxTable = $sm->get('Admin\Model\TaxTable');
                    $taxes = $TaxTable->fetchAll();
                    $options = array();
                    foreach($taxes as $tax) {
                        $options[$tax->id] = $tax->name.' - '.$tax->percentage.'%';
                    }

                    $form->get('Tax_id')->setValueOptions($options);*/
                    
                    return $form;
                },
                        
                'Zend\Session\SessionManager' => function ($sm) {
                    $config = $sm->get('config');
                    if (isset($config['session'])) {
                        $session = $config['session'];

                        $sessionConfig = null;
                        if (isset($session['config'])) {
                            $class = isset($session['config']['class'])  ? $session['config']['class'] : 'Zend\Session\Config\SessionConfig';
                            $options = isset($session['config']['options']) ? $session['config']['options'] : array();
                            $sessionConfig = new $class();
                            $sessionConfig->setOptions($options);
                        }

                        $sessionStorage = null;
                        if (isset($session['storage'])) {
                            $class = $session['storage'];
                            $sessionStorage = new $class();
                        }

                        $sessionSaveHandler = null;
                        if (isset($session['save_handler'])) {
                            // class should be fetched from service manager since it will require constructor arguments
                            $sessionSaveHandler = $sm->get($session['save_handler']);
                        }

                        $sessionManager = new SessionManager($sessionConfig, $sessionStorage, $sessionSaveHandler);

                        if (isset($session['validators'])) {
                            $chain = $sessionManager->getValidatorChain();
                            foreach ($session['validators'] as $validator) {
                                $validator = new $validator();
                                $chain->attach('session.validate', array($validator, 'isValid'));
                            }
                        }
                    } else {
                        $sessionManager = new SessionManager();
                    }
                    Container::setDefaultManager($sessionManager);
                    return $sessionManager;
                },
            ),
        );
    }
}