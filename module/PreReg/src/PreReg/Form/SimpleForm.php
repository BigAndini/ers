<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreReg\Form;

use Doctrine\Common\Persistence\ObjectManager;
use Application\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Form;

class SimpleForm extends Form
{
    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('simple');

        $this->setHydrator(new DoctrineHydrator($objectManager));

        $this->add(array(
            'type' => 'Zend\Form\Element\Csrf',
            'name' => 'csrf',
        ));

        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Send',
                'class' => 'btn btn-default',
            ),
        ));
    }
}