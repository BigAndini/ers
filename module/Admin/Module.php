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

class Module
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
    
    public function getServiceConfig() {
        return array(
            'factories' => array(
                'doctrine.entitymanager'  => new \DoctrineORMModule\Service\EntityManagerFactory('orm_default'),
                /* 
                 * Form Factories
                 */
                'Admin\Form\Product' => function($sm){
                    $form   = new Form\Product();
                    
                    $em = $sm->get('doctrine.entitymanager');
                    $taxes = $em->getRepository('ErsBase\Entity\Tax')->findAll();
                    
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
                    
                    $em = $sm->get('doctrine.entitymanager');
                    $roles = $em->getRepository('ErsBase\Entity\UserRole')->findBy(array(), array('roleId' => 'ASC'));
                    
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