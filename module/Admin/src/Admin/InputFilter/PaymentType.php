<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\InputFilter;

use Zend\InputFilter\Factory as InputFactory; 
use Zend\InputFilter\InputFilter; 
use Zend\InputFilter\InputFilterAwareInterface; 
use Zend\InputFilter\InputFilterInterface; 

class PaymentType implements InputFilterAwareInterface 
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
            'name' => 'short_description', 
            'required' => true, 
            'filters' => array( 
                array('name' => 'StripTags'), 
                array('name' => 'StringTrim'), 
            ), 
            'validators' => array( 
            ),
        ])); 
 
        $inputFilter->add($factory->createInput([ 
            'name' => 'long_description', 
            'required' => false, 
            'filters' => array( 
                array('name' => 'StringTrim'), 
            ), 
            'validators' => array( 
            ), 
        ])); 
        
        $inputFilter->add($factory->createInput([ 
            'name' => 'explanation', 
            'required' => false, 
            'filters' => array( 
                array('name' => 'StringTrim'), 
            ), 
            'validators' => array( 
            ), 
        ])); 
 
        $inputFilter->add($factory->createInput([ 
            'name' => 'fix_fee', 
            'required' => false, 
            'filters' => array( 
                array('name' => 'StripTags'), 
                array('name' => 'StringTrim'), 
            ), 
            'validators' => array( 
            ), 
        ])); 
 
        $inputFilter->add($factory->createInput([ 
            'name' => 'percentage_fee', 
            'required' => false, 
            'filters' => array( 
                array('name' => 'StripTags'), 
                array('name' => 'StringTrim'), 
            ), 
            'validators' => array( 
            ), 
        ])); 
 
        $inputFilter->add($factory->createInput([ 
            'name' => 'active_from_id', 
            'required' => true, 
            'filters' => array( 
                array('name' => 'Int'), 
            ), 
            'validators' => array( 
                array(
                    # Check if active_from and active_until are the same deadline
                    'name' => 'Callback',
                    'options' => array(
                        'messages' => array(
                            \Zend\Validator\Callback::INVALID_VALUE => "The deadlines active from and active until cannot be the same.",
                        ),
                        'callback' => function($value, $context=array()) {
                            if($value == 0 && $context['active_until_id'] == 0) {
                                return true;
                            }
                            if($value != $context['active_until_id']) {
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
                             * if active_from_id or active_until_id is 0 
                             * everything is ok, because this means either on or
                             * both deadlines are not set.
                             */
                            if(\is_numeric($value) && $value == 0) {
                                return true;
                            }
                            if(\is_numeric($context['active_until_id']) && $context['active_until_id'] == 0) {
                                return true;
                            }
                            
                            $active_from = $em->getRepository('ErsBase\Entity\Deadline')
                                ->findOneBy(array('id' => $value));
                            $active_until = $em->getRepository('ErsBase\Entity\Deadline')
                                ->findOneBy(array('id' => $context['active_until_id']));
                            
                            $diff = $active_from->getDeadline()->getTimestamp() - $active_until->getDeadline()->getTimestamp();
                            if($diff < 0) {
                                # active_from must be before active_until
                                return true;
                            }
                            
                            return false;
                        },
                    ),
                ),
            ), 
        ])); 
 
        $inputFilter->add($factory->createInput([ 
            'name' => 'active_until_id', 
            'required' => true, 
            'filters' => array( 
                array('name' => 'Int'), 
            ), 
            'validators' => [array(
                    # Check if active_from and active_until are the same deadline
                    'name' => 'Callback',
                    'options' => array(
                        'messages' => array(
                            \Zend\Validator\Callback::INVALID_VALUE => "The deadlines active from and active until cannot be the same.",
                        ),
                        'callback' => function($value, $context=array()) {
                            if($value == 0 && $context['active_from_id'] == 0) {
                                return true;
                            }
                            if($value != $context['active_from_id']) {
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
                             * if active_from_id or active_until_id is 0 
                             * everything is ok, because this means either on or
                             * both deadlines are not set.
                             */
                            if(\is_numeric($value) && $value == 0) {
                                return true;
                            }
                            if(\is_numeric($context['active_from_id']) && $context['active_from_id'] == 0) {
                                return true;
                            }
                            
                            $active_from = $em->getRepository('ErsBase\Entity\Deadline')
                                ->findOneBy(array('id' => $context['active_from_id']));
                            $active_until = $em->getRepository('ErsBase\Entity\Deadline')
                                ->findOneBy(array('id' => $value));
                            
                            $diff = $active_from->getDeadline()->getTimestamp() - $active_until->getDeadline()->getTimestamp();
                            if($diff < 0) {
                                # active_from must be before active_until
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