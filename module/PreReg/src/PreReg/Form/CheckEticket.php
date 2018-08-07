<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreReg\Form;

use Doctrine\Common\Persistence\ObjectManager;
use ErsBase\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Form;

class CheckEticket extends Form
{
    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('check-eticket');

        #$this->setHydrator(new DoctrineHydrator($objectManager));

        $this->add(array(
            'type' => 'Zend\Form\Element\Csrf',
            'name' => 'csrf',
        ));

        $this->add(array( 
            'name' => 'code', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'placeholder' => _('E-Ticket code...'), 
                'required' => 'required', 
                'class' => 'form-control form-element',
            ), 
            'options' => array( 
                'label' => _('E-Ticket code'),
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ), 
        )); 
        
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => _('Send'),
                'class' => 'btn btn-default',
            ),
        ));
    }
}