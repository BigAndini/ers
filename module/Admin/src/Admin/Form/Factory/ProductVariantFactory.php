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

class ProductVariantFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
       $form   = new Form\ProductVariant();

        $options = array();
        $options['text'] = 'Text';
        $options['date'] = 'Date';
        $options['select'] = 'Select';

        $form->get('type')->setValueOptions($options);

        return $form;
    }
}