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

use Zend\View\Helper\ServerUrl;
use Zend\View\Helper\Url as UrlHelper;
use Zend\Uri\Http as HttpUri;
use Zend\Console\Console;
use Zend\ModuleManager\Feature\ViewHelperProviderInterface;

class Module implements ViewHelperProviderInterface
{
    public function onBootstrap(MvcEvent $event) {
        $eventManager        = $event->getApplication()->getEventManager();
        $eventManager->getSharedManager()->attach('Zend\Mvc\Controller\AbstractActionController', 'dispatch', function($event) {
            $controller      = $event->getTarget();
            $controllerClass = get_class($controller);
            $moduleNamespace = substr($controllerClass, 0, strpos($controllerClass, '\\'));
            $config          = $event->getApplication()->getServiceManager()->get('config');
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
    
    public function getViewHelperConfig() {
        return array(
            'factories' => array(
                'url' => function ($helperPluginManager) {
                    $serviceLocator = $helperPluginManager->getServiceLocator();
                    $config = $serviceLocator->get('Config');
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
                'serverUrl' => function ($helperPluginManager) {
                    $serviceLocator = $helperPluginManager->getServiceLocator();
                    $config = $serviceLocator->get('Config');

                    $serverUrlHelper = new ServerUrl();
                    if (Console::isConsole()) {
                        $serverUrlHelper->setHost($config['website']['host'])
                            ->setScheme($config['website']['scheme']);
                    }

                    return $serverUrlHelper;
                },
            )
        );
    }
    
    public function getServiceConfig() {
        return array(
            'factories' => array(
                'Logger' => function($serviceManager){
                    $logger = new \Zend\Log\Logger;
                    if(!is_dir(getcwd().'/data/logs')) {
                        mkdir(getcwd().'/data/logs');
                    }
                    $writer = new \Zend\Log\Writer\Stream('./data/logs/'.date('Y-m-d').'-zend.log');
                    $logger->addWriter($writer);
                    
                    #$filter = new Zend\Log\Filter\Priority(Logger::CRIT);
                    #$writer->addFilter($filter);


                    return $logger;
                },
                'doctrine.entitymanager'  => new \DoctrineORMModule\Service\EntityManagerFactory('orm_default'),
                /* 
                 * Form Factories
                 */
                'Admin\Form\PaymentType' => 'Admin\Form\Factory\PaymentTypeFactory',
                'Admin\Form\Product' => 'Admin\Form\Factory\ProductFactory',
                'Admin\Form\ProductVariant' => 'Admin\Form\Factory\ProductVariantFactory',
                'Admin\Form\Role' => 'Admin\Form\Factory\RoleFactory',
                'Admin\Form\User' => function($serviceManager){
                    $form   = new Form\User();
                    $form->setServiceLocator($serviceManager);
                    return $form;
                },
                'Admin\InputFilter\User' => function($serviceManager){
                    $inputFilter   = new InputFilter\User();
                    $inputFilter->setServiceLocator($serviceManager);
                    return $inputFilter;
                },
                'Admin\InputFilter\AcceptBuyerChange' => function($serviceManager){
                    $inputFilter   = new InputFilter\AcceptBuyerChange();
                    $inputFilter->setServiceLocator($serviceManager);
                    return $inputFilter;
                },
                'Admin\InputFilter\AcceptParticipantChangeItem' => function($serviceManager){
                    $inputFilter   = new InputFilter\AcceptParticipantChangeItem();
                    $inputFilter->setServiceLocator($serviceManager);
                    return $inputFilter;
                },
                'Admin\InputFilter\AcceptParticipantChangePackage' => function($serviceManager){
                    $inputFilter   = new InputFilter\AcceptParticipantChangePackage();
                    $inputFilter->setServiceLocator($serviceManager);
                    return $inputFilter;
                },
                'Admin\InputFilter\AcceptMovePackage' => function($serviceManager){
                    $inputFilter   = new InputFilter\AcceptMovePackage();
                    $inputFilter->setServiceLocator($serviceManager);
                    return $inputFilter;
                },
            ),
        );
    }
}