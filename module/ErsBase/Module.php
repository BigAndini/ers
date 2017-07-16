<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ErsBase;

use Zend\Mvc\MvcEvent;
use Zend\Mvc\ModuleRouteListener;
use Zend\Session\Container;

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
        $serviceManager = $app->getServiceManager();

        $shared->attach('ZfcUser\Service\User', 'register.post', function ($e) use ($serviceManager) {
            $userService = $e->getTarget();
            $serviceManager = $userService->getServiceManager();
            $entityManager = $serviceManager->get('doctrine.entitymanager.orm_default');
            $newUser = $e->getParam('user');
            #$registrationForm = $e->getParam('form');
            $config = $serviceManager->get('config');
            $criteria = array('roleId' => $config['bjyauthorize']['new_user_default_role']);
            $defaultUserRole = $entityManager->getRepository('ErsBase\Entity\UserRole')->findOneBy($criteria);
            
            if ($defaultUserRole !== null)
            {
                $newUser->addRole($defaultUserRole);
                $entityManager->persist($newUser);
                $entityManager->flush();
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
                'ErsBase\Entity\Order' => function ($serviceManager) {
                    $entityManager = $serviceManager->get('Doctrine\ORM\EntityManager');
                    $container = new Container('ers');
                    $currency = $entityManager->getRepository('ErsBase\Entity\Currency')
                                ->findOneBy(array('short' => $container->currency));
                    $order = new Entity\Order();
                    $order->setCurrency($currency);
                    
                    return $order;
                },
                'ErsBase\Service\CodeService' => 'ErsBase\Service\Factory\CodeFactory',
                'ErsBase\Service\EmailService' => function ($serviceManager) {
                    $emailService = new Service\EmailService();
                    $emailService->setServiceLocator($serviceManager);
                    return $emailService;
                },
                'ErsBase\Service\CloneService' => function ($serviceManager) {
                    $service = new Service\CloneService();
                    $service->setServiceLocator($serviceManager);
                    return $service;
                },
                'ErsBase\Service\AgegroupService' => function($serviceManager) {
                    $agegroupService = new Service\AgegroupService();
                    $agegroupService->setServiceLocator($serviceManager);
                    
                    return $agegroupService;
                },
                'ErsBase\Service\AgegroupService:price' => function($serviceManager) {
                    $agegroupService = new Service\AgegroupService();
                    $agegroupService->setServiceLocator($serviceManager);
                    $entityManager = $serviceManager->get('Doctrine\ORM\EntityManager');
                    $agegroups = $entityManager->getRepository('ErsBase\Entity\Agegroup')
                                ->findBy(array('price_change' => '1'));
                    $agegroupService->setAgegroups($agegroups);
                    
                    return $agegroupService;
                },
                'ErsBase\Service\AgegroupService:ticket' => function($serviceManager) {
                    $agegroupService = new Service\AgegroupService();
                    $entityManager = $serviceManager->get('Doctrine\ORM\EntityManager');
                    $agegroups = $entityManager->getRepository('ErsBase\Entity\Agegroup')
                                ->findBy(array('ticket_change' => '1'));
                    $agegroupService->setAgegroups($agegroups);
                    
                    return $agegroupService;
                },
                'ErsBase\Service\DeadlineService' => function($serviceManager) {
                    $deadlineService = new Service\DeadlineService();
                    $deadlineService->setServiceLocator($serviceManager);
                    
                    return $deadlineService;
                },
                'ErsBase\Service\DeadlineService:price' => function($serviceManager) {
                    $deadlineService = new Service\DeadlineService();
                    $entityManager = $serviceManager->get('Doctrine\ORM\EntityManager');
                    $deadlines = $entityManager->getRepository('ErsBase\Entity\Deadline')
                                ->findBy(array('price_change' => '1'));
                    $deadlineService->setDeadlines($deadlines);
                    
                    return $deadlineService;
                },
                'ErsBase\Service\DeadlineService:noprice' => function($serviceManager) {
                    $deadlineService = new Service\DeadlineService();
                    $entityManager = $serviceManager->get('Doctrine\ORM\EntityManager');
                    $deadlines = $entityManager->getRepository('ErsBase\Entity\Deadline')
                                ->findBy(array('price_change' => '0'));
                    $deadlineService->setDeadlines($deadlines);
                    
                    return $deadlineService;
                },
                'ErsBase\Service\DeadlineService:all' => function($serviceManager) {
                    $deadlineService = new Service\DeadlineService();
                    $entityManager = $serviceManager->get('Doctrine\ORM\EntityManager');
                    $deadlines = $entityManager->getRepository('ErsBase\Entity\Deadline')
                                ->findAll();
                    $deadlineService->setDeadlines($deadlines);
                    
                    return $deadlineService;
                },
                'ErsBase\Service\TicketCounterService' => function($serviceManager) {
                    $ticketCounterService = new Service\TicketCounterService();
                    $ticketCounterService->setServiceLocator($serviceManager);
                    return $ticketCounterService;
                },
                'ErsBase\Service\ETicketService' => function($serviceManager) {
                    $service = new Service\ETicketService();
                    $service->setServiceLocator($serviceManager);
                    $entityManager = $serviceManager->get('Doctrine\ORM\EntityManager');
                    $products = $entityManager->getRepository('ErsBase\Entity\Product')
                                ->findBy(array('visible_on_eticket' => '1'), array('position' => 'ASC'));
                    $service->setProducts($products);
                    return $service;
                },
                'ErsBase\Service\OrderService' => function($serviceManager) {
                    $service = new Service\OrderService();
                    $service->setServiceLocator($serviceManager);
                    return $service;
                },
                'ErsBase\Service\StatusService' => function($serviceManager) {
                    $service = new Service\StatusService();
                    $service->setServiceLocator($serviceManager);
                    return $service;
                },
                'ErsBase\Service\OptionService' => function($serviceManager) {
                    $service = new Service\OptionService();
                    $service->setServiceLocator($serviceManager);
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