<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Validator;

use Zend\InputFilter\Factory as InputFactory; 
use Zend\InputFilter\InputFilter; 
use Zend\InputFilter\InputFilterAwareInterface; 
use Zend\InputFilter\InputFilterInterface; 

class PaymentTypeBankTransferValidator implements InputFilterAwareInterface 
{ 
    protected $inputFilter; 
    protected $em;
    
    public function setEntityManager(\Doctrine\ORM\EntityManager $em) {
        $this->em = $em;
    }
    public function getEntityManager() {
        return $this->em;
    }
    
    public function setInputFilter(InputFilterInterface $inputFilter) 
    { 
        throw new \Exception("Not used"); 
    } 
    
    public function getInputFilter() 
    { 
        if (!$this->inputFilter) 
        { 
            $inputFilter = new InputFilter(); 
            $factory = new InputFactory(); 
            
        
        $inputFilter->add($factory->createInput([ 
            'name' => 'name', 
            'required' => true, 
            'filters' => array( 
                array('name' => 'StripTags'), 
                array('name' => 'StringTrim'), 
            ), 
            'validators' => array( 
            ), 
        ])); 
 
        $inputFilter->add($factory->createInput([ 
            'name' => 'shortDescription', 
            'required' => true, 
            'filters' => array( 
                array('name' => 'StripTags'), 
                array('name' => 'StringTrim'), 
            ), 
            'validators' => array( 
            ), 
        ])); 
 
        $inputFilter->add($factory->createInput([ 
            'name' => 'longDescription', 
            'required' => false, 
            'filters' => array( 
                array('name' => 'StripTags'), 
                array('name' => 'StringTrim'), 
            ), 
            'validators' => array( 
            ), 
        ])); 
 
        $inputFilter->add($factory->createInput([ 
            'name' => 'fixFee', 
            'required' => false, 
            'filters' => array( 
                array('name' => 'StripTags'), 
                array('name' => 'StringTrim'), 
            ), 
            'validators' => array( 
            ), 
        ])); 
 
        $inputFilter->add($factory->createInput([ 
            'name' => 'percentageFee', 
            'required' => false, 
            'filters' => array( 
                array('name' => 'StripTags'), 
                array('name' => 'StringTrim'), 
            ), 
            'validators' => array( 
            ), 
        ])); 
 
        $inputFilter->add($factory->createInput([ 
            'name' => 'activeFrom_id', 
            'required' => true, 
            'filters' => array( 
                array('name' => 'Int'), 
            ), 
            'validators' => array( 
                array(
                    # Check if activeFrom and activeUntil are the same deadline
                    'name' => 'Callback',
                    'options' => array(
                        'messages' => array(
                            \Zend\Validator\Callback::INVALID_VALUE => "The deadlines active from and active until cannot be the same.",
                        ),
                        'callback' => function($value, $context=array()) {
                            if($value == 0 && $context['activeUntil_id'] == 0) {
                                return true;
                            }
                            if($value != $context['activeUntil_id']) {
                                return true;
                            }
                            return false;
                        },
                    ),
                ),
                array(
                    'name' => 'Callback',
                    'options' => array(
                        'messages' => array(
                            \Zend\Validator\Callback::INVALID_VALUE => "The deadline active from may not be before active until.",
                        ),
                        'callback' => function($value, $context=array()) {
                            $em = $this->getEntityManager();
                            /*
                             * if activeFrom_id or activeUntil_id is 0 
                             * everything is ok, because this means either on or
                             * both deadlines are not set.
                             */
                            if(\is_numeric($value) && $value == 0) {
                                return true;
                            }
                            if(\is_numeric($context['activeUntil_id']) && $context['activeUntil_id'] == 0) {
                                return true;
                            }
                            
                            $activeFrom = $em->getRepository("ersEntity\Entity\Deadline")
                                ->findOneBy(array('id' => $value));
                            $activeUntil = $em->getRepository("ersEntity\Entity\Deadline")
                                ->findOneBy(array('id' => $context['activeUntil_id']));
                            
                            $diff = $activeFrom->getDeadline()->getTimestamp() - $activeUntil->getDeadline()->getTimestamp();
                            if($diff < 0) {
                                # activeFrom must be before activeUntil
                                return true;
                            }
                            
                            return false;
                        },
                    ),
                ),
            ), 
        ])); 
 
        $inputFilter->add($factory->createInput([ 
            'name' => 'activeUntil_id', 
            'required' => true, 
            'filters' => array( 
                array('name' => 'Int'), 
            ), 
            'validators' => [array(
                    # Check if activeFrom and activeUntil are the same deadline
                    'name' => 'Callback',
                    'options' => array(
                        'messages' => array(
                            \Zend\Validator\Callback::INVALID_VALUE => "The deadlines active from and active until cannot be the same.",
                        ),
                        'callback' => function($value, $context=array()) {
                            if($value == 0 && $context['activeFrom_id'] == 0) {
                                return true;
                            }
                            if($value != $context['activeFrom_id']) {
                                return true;
                            }
                            return false;
                        },
                    ),
                ),
                array(
                    'name' => 'Callback',
                    'options' => array(
                        'messages' => array(
                            \Zend\Validator\Callback::INVALID_VALUE => "The deadline active from may not be before active until.",
                        ),
                        'callback' => function($value, $context=array()) {
                            $em = $this->getEntityManager();
                            /*
                             * if activeFrom_id or activeUntil_id is 0 
                             * everything is ok, because this means either on or
                             * both deadlines are not set.
                             */
                            if(\is_numeric($value) && $value == 0) {
                                return true;
                            }
                            if(\is_numeric($context['activeFrom_id']) && $context['activeFrom_id'] == 0) {
                                return true;
                            }
                            
                            $activeFrom = $em->getRepository("ersEntity\Entity\Deadline")
                                ->findOneBy(array('id' => $context['activeFrom_id']));
                            $activeUntil = $em->getRepository("ersEntity\Entity\Deadline")
                                ->findOneBy(array('id' => $value));
                            
                            $diff = $activeFrom->getDeadline()->getTimestamp() - $activeUntil->getDeadline()->getTimestamp();
                            if($diff < 0) {
                                # activeFrom must be before activeUntil
                                return true;
                            }
                            
                            return false;
                        },
                    ),
                ),
                ], 
        ])); 
 
        $inputFilter->add($factory->createInput([ 
            'name' => 'days2pay', 
            'required' => false, 
            'filters' => array( 
                array('name' => 'StripTags'), 
                array('name' => 'StringTrim'), 
            ), 
            'validators' => array( 
            ), 
        ])); 
 
            $this->inputFilter = $inputFilter; 
        } 
        
        return $this->inputFilter; 
    } 
} 