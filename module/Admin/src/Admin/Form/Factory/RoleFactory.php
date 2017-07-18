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

class RoleFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $form = new Form\Role();

        $entityManager = $serviceLocator->get('doctrine.entitymanager');
        $roles = $entityManager->getRepository('ErsBase\Entity\Role')
                ->findBy(array(), array('roleId' => 'ASC'));

        $options = array();
        $options[null] = 'no parent';
        foreach($roles as $role) {
            $options[$role->getId()] = $role->getRoleId();
        }

        $form->get('Parent_id')->setValueOptions($options);

        return $form;
    }
}