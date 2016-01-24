<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ErsBase;

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
            $defaultUserRole = $em->getRepository('ErsBase\Entity\UserRole')->findOneBy($criteria);
            
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
                'ErsBase\Service\CodeService' => 'ErsBase\Service\Factory\CodeFactory',
                'ErsBase\Service\EmailService' => function ($sm) {
                    $emailService = new Service\EmailService();
                    $emailService->setServiceLocator($sm);
                    return $emailService;
                },
                'ErsBase\Service\CloneService' => function ($sm) {
                    $service = new Service\CloneService();
                    $service->setServiceLocator($sm);
                    return $service;
                },
                'ErsBase\Service\AgegroupService:price' => function($sm) {
                    $agegroupService = new Service\AgegroupService();
                    $em = $sm->get('Doctrine\ORM\EntityManager');
                    $agegroups = $em->getRepository('ErsBase\Entity\Agegroup')
                                ->findBy(array('price_change' => '1'));
                    $agegroupService->setAgegroups($agegroups);
                    
                    return $agegroupService;
                },
                'ErsBase\Service\AgegroupService:ticket' => function($sm) {
                    $agegroupService = new Service\AgegroupService();
                    $em = $sm->get('Doctrine\ORM\EntityManager');
                    $agegroups = $em->getRepository('ErsBase\Entity\Agegroup')
                                ->findBy(array('ticket_change' => '1'));
                    $agegroupService->setAgegroups($agegroups);
                    
                    return $agegroupService;
                },
                'ErsBase\Service\DeadlineService:price' => function($sm) {
                    $deadlineService = new Service\DeadlineService();
                    $em = $sm->get('Doctrine\ORM\EntityManager');
                    $deadlines = $em->getRepository('ErsBase\Entity\Deadline')
                                ->findBy(array('price_change' => '1'));
                    $deadlineService->setDeadlines($deadlines);
                    
                    return $deadlineService;
                },
                'ErsBase\Service\DeadlineService:noprice' => function($sm) {
                    $deadlineService = new Service\DeadlineService();
                    $em = $sm->get('Doctrine\ORM\EntityManager');
                    $deadlines = $em->getRepository('ErsBase\Entity\Deadline')
                                ->findBy(array('price_change' => '0'));
                    $deadlineService->setDeadlines($deadlines);
                    
                    return $deadlineService;
                },
                'ErsBase\Service\DeadlineService:all' => function($sm) {
                    $deadlineService = new Service\DeadlineService();
                    $em = $sm->get('Doctrine\ORM\EntityManager');
                    $deadlines = $em->getRepository('ErsBase\Entity\Deadline')
                                ->findAll();
                    $deadlineService->setDeadlines($deadlines);
                    
                    return $deadlineService;
                },
                'ErsBase\Service\TicketCounterService' => function($sm) {
                    $ticketCounterService = new Service\TicketCounterService();
                    $ticketCounterService->setServiceLocator($sm);
                    return $ticketCounterService;
                },
                'ErsBase\Service\ETicketService' => function($sm) {
                    $service = new Service\ETicketService();
                    $service->setServiceLocator($sm);
                    $em = $sm->get('Doctrine\ORM\EntityManager');
                    $products = $em->getRepository('ErsBase\Entity\Product')
                                ->findBy(array('visible' => '1'), array('ordering' => 'ASC'));
                    $service->setProducts($products);
                    return $service;
                },
                'ErsBase\Service\OrderService' => function($sm) {
                    $service = new Service\OrderService();
                    $service->setServiceLocator($sm);
                    return $service;
                },
                'ErsBase\Service\OptionService' => function($sm) {
                    $service = new Service\OptionService();
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
                    $validator = new \ErsBase\Validator\NotEmptyAllowZero;
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