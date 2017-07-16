<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreReg\InputFilter;

use Zend\InputFilter\Factory as InputFactory; 
use Zend\InputFilter\InputFilter; 
use Zend\InputFilter\InputFilterAwareInterface; 
use Zend\InputFilter\InputFilterInterface; 

class ResetPassword implements InputFilterAwareInterface 
{ 
    protected $inputFilter; 
    protected $entityManager;
    
    public function setEntityManager(\Doctrine\ORM\EntityManager $entityManager) {
        $this->em = $entityManager;
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
                'name' => 'newPassword', 
                'required' => false, 
                'filters' => array( 
                    array('name' => 'StripTags'), 
                    array('name' => 'StringTrim'), 
                ), 
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'min' => 6,
                        ),
                    ),
                    array(
                        'name' => 'Callback',
                        'options' => array(
                            'messages' => array(
                                \Zend\Validator\Callback::INVALID_VALUE => 'Your passwords do not match.',
                            ),
                            'callback' => function($value, $context=array()) {
                                /*
                                 * If the buyer_id is not 0 the user adds an 
                                 * already existing participant as buyer.
                                 */
                                if(!isset($context['newPassword2'])) {
                                    return false;
                                }
                                if($value == $context['newPassword2']) {
                                    return true;
                                }
                                return false;
                            },
                        ),
                    ),
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 45,
                        ),
                    ),
                ), 
            ])); 

            $inputFilter->add($factory->createInput([ 
                'name' => 'surname', 
                'required' => false, 
                'filters' => array( 
                    array('name' => 'StripTags'), 
                    array('name' => 'StringTrim'), 
                ), 
                'validators' => array( 
                    array(
                        'name' => 'Callback',
                        'options' => array(
                            'messages' => array(
                                \Zend\Validator\Callback::INVALID_VALUE => 'The Surname of the buyer cannot be empty.',
                            ),
                            'callback' => function($value, $context=array()) {
                                /*
                                 * If the buyer_id is not 0 the user adds an 
                                 * already existing participant as buyer.
                                 */
                                if(isset($context['buyer_id']) && $context['buyer_id'] != 0) {
                                    return true;
                                }
                                
                                if($value != '') {
                                    return true;
                                }
                                return false;
                            },
                            
                        ),
                    ),
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 45,
                        ),
                    ),
                    array ( 
                        'name' => 'Callback', 
                        'options' => array(
                            'messages' => array(
                                \Zend\Validator\Callback::INVALID_VALUE => 'The provided name contains invalid character. These charaters are not allowed: !"§$%()=<>|^;{}[]',
                            ),
                            'callback' => function($value, $context=array()) {
                                $alphabet = '!"§$%()=<>|^;{}[]';
                                $alpha = str_split($alphabet);
                                foreach($alpha as $char) {
                                    if(strstr($value, $char)) {
                                        return false;
                                    }
                                }
                                return true;
                            },
                            
                        ),
                    ),
                ), 
            ])); 

            $inputFilter->add($factory->createInput([ 
                'name' => 'email', 
                'required' => false,
                'filters' => array( 
                    array('name' => 'StripTags'), 
                    array('name' => 'StringTrim'), 
                ), 
                'validators' => array( 
                    array(
                        'name' => 'Callback',
                        'options' => array(
                            'messages' => array(
                                \Zend\Validator\Callback::INVALID_VALUE => 'The email address of the buyer cannot be empty.',
                            ),
                            'callback' => function($value, $context=array()) {
                                /*
                                 * If the buyer_id is not 0 the user adds an 
                                 * already existing participant as buyer.
                                 */
                                if(isset($context['buyer_id']) && $context['buyer_id'] != 0) {
                                    return true;
                                }
                                
                                if($value != '') {
                                    
                                    return true;
                                }
                                return false;
                            },
                            
                        ),
                    ),
                    array ( 
                        'name' => 'EmailAddress', 
                        'options' => array( 
                            'messages' => array( 
                                'emailAddressInvalidFormat' => 'Email address format is not invalid', 
                            ) 
                        ), 
                    ), 
                    array ( 
                        'name' => 'NotEmpty', 
                        'options' => array( 
                            'messages' => array( 
                                'isEmpty' => '', 
                            ) 
                        ), 
                    ), 
                ), 
            ])); 
 
            $this->inputFilter = $inputFilter; 
        } 
        
        return $this->inputFilter; 
    } 
} 