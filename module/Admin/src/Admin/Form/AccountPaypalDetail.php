<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Form;

use Doctrine\Common\Persistence\ObjectManager;
use ErsBase\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Form;

class AccountPaypalDetail extends Form
{
    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('account-paypal-detail');

        $this->setHydrator(new DoctrineHydrator($objectManager));

        /*
         * sandbox_mode
         * client_id
         * client_secret
         * log_file
         */
        
        
        $this->add(array(
            'name' => 'sandbox_mode',
            'type' => 'checkbox',
            'attributes' => array(
                'class' => 'checkbox',
            ),
            'options' => array(
                'label' => 'Sandbox Mode',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
        
        $this->add(array(
            'name' => 'client_id',
            'attributes' => array(
                'required' => 'required',
                'type'  => 'text',
                'class' => 'form-control form-element',
                'placeholder' => 'Client Id...',
            ),
            'options' => array(
                'label' => 'Client Id',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
        
        $this->add(array(
            'name' => 'client_secret',
            'attributes' => array(
                'required' => 'required',
                'type'  => 'text',
                'class' => 'form-control form-element',
                'placeholder' => 'Client Secret...',
            ),
            'options' => array(
                'label' => 'Client Secret',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
        
        $this->add(array(
            'name' => 'log_file',
            'attributes' => array(
                'required' => 'required',
                'type'  => 'text',
                'class' => 'form-control form-element',
                'placeholder' => 'Logfile...',
            ),
            'options' => array(
                'label' => 'Logfile',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Csrf',
            'name' => 'csrf',
        ));

        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Send',
                'class' => 'btn btn-success',
            ),
        ));
    }
}