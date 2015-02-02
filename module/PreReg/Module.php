<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//  module/PreReg/Module.php
namespace PreReg;

#use PreReg\Model;
use ersEntity\Entity;
use Zend\Mvc\ModuleRouteListener;
use Zend\Session\SessionManager;
use Zend\Session\Container;

class Module
{
    
    public function onBootstrap($e)
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
        $this->bootstrapSession($e);
    }

    public function bootstrapSession($e)
    {
        $session = $e->getApplication()
                     ->getServiceManager()
                     ->get('Zend\Session\SessionManager');
        $session->start();
        
        if($session->isValid()) {
            error_log('Session is valid');
        } else {
            error_log('Session is not valid');
        }
        
        error_log($_SESSION['__ZF']['_REQUEST_ACCESS_TIME']);
        if(isset($_SESSION['__ZF']['_REQUEST_ACCESS_TIME'])) {
            $filename = $_SESSION['__ZF']['_REQUEST_ACCESS_TIME'].".txt";
            $publicDir = getcwd() . '/sessions';
            file_put_contents($publicDir.'/'.$filename, var_export($_SESSION,true));
        }
        #error_log(var_export($_SESSION, true));
        
        $container = new Container('initialized');
        error_log($container->lifetime.' vs. '.(time()-3600));
        if (!isset($container->init) || $container->lifetime < (time()-3600)) {
            error_log('Reset Session');
            #$_SESSION = array();
            /*$session = $e->getApplication()
                     ->getServiceManager()
                     ->get('Zend\Session\SessionManager');
            $session->start();*/
        
            $container = new Container('initialized');
            $container->init = 1;
            # session is valid for one hour.
            $container->lifetime = time()+3600;
        }
        
        $session_cart = new Container('cart');
        #$session_cart->getManager()->getStorage()->clear('cart');
        if(!isset($session_cart->init) || $session_cart->init != 1) {
            $session_cart->getManager()->getStorage()->clear('cart');
            $session_cart->order = new Entity\Order();
            $session_cart->init = 1;
        } else {
            error_log('CART is already initialized'); 
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
                 * Form Factories
                 */
                'PreReg\Form\ProductViewForm' => function($sm){
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