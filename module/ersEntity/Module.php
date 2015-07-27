<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ersEntity;

use Zend\Mvc\MvcEvent;
use Zend\Mvc\ModuleRouteListener;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $app = $e->getApplication();
        $eventManager = $app->getEventManager();
        #$eventManager        = $e->getApplication()->getEventManager();
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
        
        
        $shared = $eventManager->getSharedManager();
        $sm = $app->getServiceManager();

        $shared->attach('ZfcUser\Service\User', 'register.post', function ($e) use ($sm) {
            $userService = $e->getTarget();
            $sm = $userService->getServiceManager();
            $em = $sm->get('doctrine.entitymanager.orm_default');
            $newUser = $e->getParam('user');
            #$registrationForm = $e->getParam('form');
            $config = $sm->get('config');
            $criteria = array('roleId' => $config['bjyauthorize']['new_user_default_role']);
            $defaultUserRole = $em->getRepository('ersEntity\Entity\UserRole')->findOneBy($criteria);
            
            if ($defaultUserRole !== null)
            {
                $newUser->addRole($defaultUserRole);
                $em->persist($newUser);
                $em->flush();
            }
        });
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
                'ersEntity\Service\CodeService' => 'ersEntity\Service\Factory\CodeFactory',
                'ersEntity\Service\EmailService' => function ($sm) {
                    $emailService = new Service\EmailService();
                    $emailService->setServiceLocator($sm);
                    return $emailService;
                },
                'ersEntity\Service\CloneService' => function ($sm) {
                    $service = new Service\CloneService();
                    $service->setServiceLocator($sm);
                    return $service;
                },
            ),
        );
    }
    
    public function getValidatorConfig() {
        return array(
            'factories' => array(
                'NotEmptyAllowZero' => function() {
                    $validator = new \ersEntity\Validator\NotEmptyAllowZero;
                    return $validator;
                }
            ),
            'validators' => array(
                'invokables' => array(
                    
                ),
            ),
        );
    }
}