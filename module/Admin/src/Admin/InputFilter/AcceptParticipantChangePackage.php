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

class AcceptParticipantChangePackage implements InputFilterAwareInterface 
{ 
    protected $inputFilter; 
    protected $sm;
    
    public function setServiceLocator($sm) {
        $this->sm = $sm;
    }
    
    public function getServiceLocator() {
        return $this->sm;
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
                'name' => 'package_id', 
                'required' => true, 
                'filters' => array( 
                    array("name" => "Int"),
                ), 
                'validators' => array(
                    array ( 
                        'name' => 'Callback', 
                        'options' => array(
                            'messages' => array(
                                \Zend\Validator\Callback::INVALID_VALUE => 'It was not possible to find the package to change the participant for.',
                            ),
                            'callback' => function($value, $context=array()) {
                                # check if order with the id of $value exists
                                if(!is_numeric($value)) {
                                    return false;
                                }
                
                                $entityManager = $this->getServiceLocator()
                                    ->get('Doctrine\ORM\EntityManager');
                                
                                $package = $entityManager->getRepository('ErsBase\Entity\Package')
                                    ->findOneBy(array('id' => $value));
                
                                if($package) {
                                    return true;
                                }
                                return false;
                            },
                            
                        ),
                    ),
                ), 
            ])); 
            
            $inputFilter->add($factory->createInput([ 
                'name' => 'user_id', 
                'required' => true, 
                'filters' => array( 
                    array("name" => "Int"),
                ), 
                'validators' => array(
                    array ( 
                        'name' => 'Callback', 
                        'options' => array(
                            'messages' => array(
                                \Zend\Validator\Callback::INVALID_VALUE => 'It was not possible to find the user that should be set as participant.',
                            ),
                            'callback' => function($value, $context=array()) {
                                # check if user with the id of $value exists
                                if(!is_numeric($value)) {
                                    return false;
                                }
                
                                $entityManager = $this->getServiceLocator()
                                    ->get('Doctrine\ORM\EntityManager');
                                
                                $user = $entityManager->getRepository('ErsBase\Entity\User')
                                    ->findOneBy(array('id' => $value));
                
                                if($user) {
                                    return true;
                                }
                                return false;
                            },
                            
                        ),
                    ),
                ), 
            ])); 

            $inputFilter->add($factory->createInput([ 
                'name' => 'comment', 
                'required' => true, 
                'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ), 
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                    ),
                ), 
            ])); 
                            
            $this->inputFilter = $inputFilter; 
        } 
        
        return $this->inputFilter; 
    }
} 