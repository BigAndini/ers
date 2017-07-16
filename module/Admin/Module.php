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
    public function onBootstrap(MvcEvent $e) {
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
    
    public function getViewHelperConfig() {
        return array(
            'factories' => array(
                'url' => function ($helperPluginManager) {
                    $serviceLocator = $helperPluginManager->getServiceLocator();
                    $config = $serviceLocator->get('Config');

                    $viewHelper =  new UrlHelper();

                    $routerName = Console::isConsole() ? 'HttpRouter' : 'Router';

                    /** @var \Zend\Mvc\Router\Http\TreeRouteStack $router */
                    $router = $serviceLocator->get($routerName);

                    if (Console::isConsole()) {
                        $requestUri = new HttpUri();
                        $requestUri->setHost($config['website']['host'])
                            ->setScheme($config['website']['scheme']);
                        $router->setRequestUri($requestUri);
                        $router->setBaseUrl($config['website']['path']);
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
                'Logger' => function($sm){
                    $logger = new \Zend\Log\Logger;
                    if(!is_dir(getcwd().'/data/log')) {
                        mkdir(getcwd().'/data/log');
                    }
                    $writer = new \Zend\Log\Writer\Stream('./data/log/'.date('Y-m-d').'-zend-error.log');
                    $logger->addWriter($writer);

                    return $logger;
                },
                'doctrine.entitymanager'  => new \DoctrineORMModule\Service\EntityManagerFactory('orm_default'),
                /* 
                 * Form Factories
                 */
                'Admin\Form\PaymentType' => function($sm) {
                    $form = new Form\PaymentType();
                    $form->get('submit')->setValue('Save');

                    $optionService = $sm->get('ErsBase\Service\OptionService');
                    #$deadlineOptions = $this->buildDeadlineOptions();
                    $deadlineOptions = $optionService->getDeadlineOptions();
                    $form->get('active_from_id')->setAttribute('options', $deadlineOptions);
                    $form->get('active_until_id')->setAttribute('options', $deadlineOptions);
                    #$form->get('active_from_id')->setValue(0);
                    #$form->get('active_until_id')->setValue(0);
                    $currencyOptions = $optionService->getCurrencyOptions();
                    $form->get('currency_id')->setAttribute('options', $currencyOptions);

                    $typeOptions = [
                        [
                            'value' => '',
                            'label' => 'Select type ...',
                            'disabled' => true,
                            'selected' => true,
                        ],
                        [
                            'value' => 'sepa',
                            'label' => 'Sepa Bank Account',
                        ],
                        [
                            'value' => 'ukbt',
                            'label' => 'UK Bank Account',
                        ],
                        [
                            'value' => 'ipayment',
                            'label' => 'iPayment Account',
                        ],
                        [
                            'value' => 'paypal',
                            'label' => 'Paypal Account',
                        ],
                    ];
                    $form->get('type')->setAttribute('options', $typeOptions);
                    
                    return $form;
                },
                'Admin\Form\Product' => function($sm){
                    $form   = new Form\Product();
                    
                    $entityManager = $sm->get('doctrine.entitymanager');
                    $taxes = $entityManager->getRepository('ErsBase\Entity\Tax')->findAll();
                    
                    $options = array();
                    foreach($taxes as $tax) {
                        $options[$tax->getId()] = $tax->getName().' - '.$tax->getPercentage().'%';
                    }

                    $form->get('tax_id')->setValueOptions($options);
                    
                    $ticketTemplates = array(
                        'default' => 'Default',
                        'weekticket' => 'Week Ticket',
                        'dayticket' => 'Day Ticket',
                        'galashow' => 'Gala-Show Ticket',
                        'clothes' => 'T-Shirt and Hoodie',
                    );
                    
                    $form->get('ticket_template')->setValueOptions($ticketTemplates);
                    
                    return $form;
                },
                'Admin\Form\Role' => function($sm){
                    $form = new Form\Role();
                    
                    $entityManager = $sm->get('doctrine.entitymanager');
                    $roles = $entityManager->getRepository('ErsBase\Entity\UserRole')->findBy(array(), array('roleId' => 'ASC'));
                    
                    $options = array();
                    $options[null] = 'no parent';
                    foreach($roles as $role) {
                        $options[$role->getId()] = $role->getRoleId();
                    }

                    $form->get('Parent_id')->setValueOptions($options);
                    
                    return $form;
                },
                'Admin\Form\ProductVariant' => function($sm){
                    $form   = new Form\ProductVariant();
                    
                    $options = array();
                    $options['text'] = 'Text';
                    $options['date'] = 'Date';
                    $options['select'] = 'Select';

                    $form->get('type')->setValueOptions($options);
                    
                    return $form;
                },
                'Admin\Form\User' => function($sm){
                    $form   = new Form\User();
                    $form->setServiceLocator($sm);
                    return $form;
                },
                'Admin\InputFilter\User' => function($sm){
                    $inputFilter   = new InputFilter\User();
                    $inputFilter->setServiceLocator($sm);
                    return $inputFilter;
                },
                'Admin\InputFilter\AcceptBuyerChange' => function($sm){
                    $inputFilter   = new InputFilter\AcceptBuyerChange();
                    $inputFilter->setServiceLocator($sm);
                    return $inputFilter;
                },
                'Admin\InputFilter\AcceptParticipantChangeItem' => function($sm){
                    $inputFilter   = new InputFilter\AcceptParticipantChangeItem();
                    $inputFilter->setServiceLocator($sm);
                    return $inputFilter;
                },
                'Admin\InputFilter\AcceptParticipantChangePackage' => function($sm){
                    $inputFilter   = new InputFilter\AcceptParticipantChangePackage();
                    $inputFilter->setServiceLocator($sm);
                    return $inputFilter;
                },
                'Admin\InputFilter\AcceptMovePackage' => function($sm){
                    $inputFilter   = new InputFilter\AcceptMovePackage();
                    $inputFilter->setServiceLocator($sm);
                    return $inputFilter;
                },
            ),
        );
    }
}