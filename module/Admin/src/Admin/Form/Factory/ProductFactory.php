<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Form\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Admin\Form;

class ProductFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $form   = new Form\Product();

        $entityManager = $serviceLocator->get('doctrine.entitymanager');
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
    }
}