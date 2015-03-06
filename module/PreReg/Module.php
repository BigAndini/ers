<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreReg;

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
        
        $sm   = $e->getApplication()->getServiceManager();
        $auth = $sm->get('BjyAuthorize\Service\Authorize');

        if(!\Zend\Console\Console::isConsole()) {
            $acl  = $auth->getAcl();
            $role = $auth->getIdentity();
            \Zend\View\Helper\Navigation::setDefaultAcl($acl);
            \Zend\View\Helper\Navigation::setDefaultRole($role);
        }
    }
    
    public function bootstrapSession($e)
    {
        $session = $e->getApplication()
                     ->getServiceManager()
                     ->get('Zend\Session\SessionManager');
        $session->start();
        
        #error_log(var_export($_SESSION, true));
        
        $container = new Container('initialized');
        
        $expiration_time = 3600;
        $container->setExpirationSeconds( $expiration_time, 'initialized' );
        if(!$session->isValid()) {
            error_log('Session is not valid');
            $container->init = 0;
        }
        #$container->getManager()->getStorage()->clear('initialized');
        if (!isset($container->init) || $container->lifetime < time()) {
            error_log('reset session');
            $container->getManager()->getStorage()->clear('initialized');
            $container = new Container('initialized');
            $container->init = 1;
            $container->lifetime = time()+$expiration_time;
            
            $container->getManager()->getStorage()->clear('cart');
        } else {
            $container->lifetime = time()+$expiration_time;
        }
        
        $session_cart = new Container('cart');
        #$session_cart->getManager()->getStorage()->clear('cart');
        if(!isset($session_cart->init) || $session_cart->init != 1) {
            error_log('reset cart');
            $session_cart->getManager()->getStorage()->clear('cart');
            $session_cart->order = new Entity\Order();
            $session_cart->init = 1;
        }
        /*
         * shopping cart debugging
         */
        /*error_log('=== Order Info ===');
        error_log('paymenttype_id: '.$session_cart->order->getPaymentTypeId());
        error_log('purchaser_id: '.$session_cart->order->getPurchaserId());
        $purchaser = $session_cart->order->getPurchaser();
        if($purchaser) {
            error_log('purchaser email: '.$session_cart->order->getPurchaser()->getEmail());
        }
        foreach($session_cart->order->getPackages() as $package) {
            error_log('  --- Package Info ---');
            error_log('  participant_id: '.$package->getParticipantId());
            $items = $package->getItems();
            foreach($items as $item) {
                error_log(' - '.$item->getName().' (Product_id: '.$item->getProductId().')');
            }
            error_log('  --------------------');
        }
        error_log('==================');*/
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