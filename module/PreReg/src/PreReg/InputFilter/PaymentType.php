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

class PaymentType implements InputFilterAwareInterface 
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
                'name' => 'paymenttype_id', 
                'required' => true, 
                'filters' => array( 
                    array('name' => 'Int'), 
                ),
                'validators' => array(
                    array ( 
                        'name' => 'Callback', 
                        'options' => array(
                            'messages' => array(
                                \Zend\Validator\Callback::INVALID_VALUE => 'Please choose an available payment type.',
                            ),
                            'callback' => function($value, $context=array()) {
                                # check if order with the id of $value exists
                                if(!is_numeric($value)) {
                                    return false;
                                }
                
                                $em = $this->getServiceLocator()
                                    ->get('Doctrine\ORM\EntityManager');
                                
                                $paymenttype = $em->getRepository("ersBase\Entity\PaymentType")
                                    ->findOneBy(array('id' => $value));
                                
                                $now = new \DateTime();
                                if(
                                    $now->getTimestamp() < $paymenttype->getActiveUntil()->getDeadline()->getTimestamp() &&
                                    $now->getTimestamp() > $paymenttype->getActiveFrom()->getDeadline()->getTimestamp()
                                    ) {
                                    return true;
                                }
                                
                                return false;
                            },
                            
                        ),
                    ),
                ),
            ]));
            
            $this->inputFilter = $inputFilter; 
        } 
        
        return $this->inputFilter; 
    } 
} 