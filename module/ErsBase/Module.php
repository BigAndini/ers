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
                'ErsBase\Entity\Order' => function ($sm) {
                    $em = $sm->get('Doctrine\ORM\EntityManager');
                    $container = new Container('ers');
                    $currency = $em->getRepository('ErsBase\Entity\Currency')
                                ->findOneBy(array('short' => $container->currency));
                    $order = new Entity\Order();
                    $order->setCurrency($currency);
                    
                    return $order;
                },
                'ErsBase\Service\CodeService' => 'ErsBase\Service\Factory\CodeFactory',
                'ErsBase\Service\EmailService' => function ($sm) {
                    $emailService = new Service\EmailService();
                    $emailService->setServiceLocator($sm);
                    return $emailService;
                },
                'ErsBase\Service\SettingService' => function ($sm) {
                    $settingService = new Service\SettingService();
                    $settingService->setServiceLocator($sm);
                    return $settingService;
                },
                'ErsBase\Service\CloneService' => function ($sm) {
                    $service = new Service\CloneService();
                    $service->setServiceLocator($sm);
                    return $service;
                },
                'ErsBase\Service\AgegroupService' => function($sm) {
                    $agegroupService = new Service\AgegroupService();
                    $agegroupService->setServiceLocator($sm);
                    
                    return $agegroupService;
                },
                'ErsBase\Service\AgegroupService:price' => function($sm) {
                    $agegroupService = new Service\AgegroupService();
                    $agegroupService->setServiceLocator($sm);
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
                'ErsBase\Service\DeadlineService' => function($sm) {
                    $deadlineService = new Service\DeadlineService();
                    $deadlineService->setServiceLocator($sm);
                    
                    return $deadlineService;
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
                                ->findBy(array('visible_on_eticket' => '1'), array('position' => 'ASC'));
                    $service->setProducts($products);
                    return $service;
                },
                'ErsBase\Service\OrderService' => function($sm) {
                    $service = new Service\OrderService();
                    $service->setServiceLocator($sm);
                    return $service;
                },
                'ErsBase\Service\PackageService' => function($sm) {
                    $service = new Service\PackageService();
                    $service->setServiceLocator($sm);
                    return $service;
                },
                'ErsBase\Service\StatusService' => function($sm) {
                    $service = new Service\StatusService();
                    $service->setServiceLocator($sm);
                    return $service;
                },
                'ErsBase\Service\ShortcodeService' => function($sm) {
                    $service = new Service\ShortcodeService();
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
    
    public function getViewHelperConfig() {
        return array(
            'factories' => array(
                'url' => function ($helperPluginManager) {
                    $serviceLocator = $helperPluginManager->getServiceLocator();
                    #$config = $serviceLocator->get('Config');
                    $settingService = $serviceLocator->get('ErsBase\Service\SettingService');

                    $viewHelper =  new UrlHelper();

                    $routerName = Console::isConsole() ? 'HttpRouter' : 'Router';

                    /** @var \Zend\Mvc\Router\Http\TreeRouteStack $router */
                    $router = $serviceLocator->get($routerName);

                    if (Console::isConsole()) {
                        if(
                                empty($settingService->get('website.host')) ||
                                empty($settingService->get('website.scheme')) ||
                                empty($settingService->get('website.path'))
                                ) {
                            throw new \Exception('Please configure the setting website.host, website.scheme and website.path for console urls.');
                        }
                        
                        $requestUri = new HttpUri();
                        
                        $requestUri->setHost($settingService->get('website.host'))
                            ->setScheme($settingService->get('website.scheme'));
                        $router->setRequestUri($requestUri);
                        $router->setBaseUrl($settingService->get('website.path'));
                    }

                    $viewHelper->setRouter($router);

                    $match = $serviceLocator->get('application')
                        ->getMvcEvent()
                        ->getRouteMatch();

                    if ($match instanceof RouteMatch) {
                        $viewHelper->setRouteMatch($match);
                    }

                    return $viewHelper;
                },
            )
        );
    }
}