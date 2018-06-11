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

class AccountPaypalDetail implements InputFilterAwareInterface 
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
                'name' => 'client_secret',
                'required' => true,
                'validators' => array(
                    array(
                        'name' => 'Callback', 
                        'options' => array(
                            'messages' => array(
                                \Zend\Validator\Callback::INVALID_VALUE => 'The provided credentials to PayPal do not seem to be valid.',
                            ),
                            'callback' => function($value, $context=array()) {
                                $client_id = $context['client_id'];
                                $client_secret = $context['client_secret'];
                                $sandbox_mode = $context['sandbox_mode'];
                                $log_file = $context['log_file'];

                                // create a temporary payment type entity for the PayPalService to use
                                $pt = new \ErsBase\Entity\PaymentType();
                                $pt->setSandboxMode($sandbox_mode);
                                $pt->setClientId($client_id);
                                $pt->setClientSecret($client_secret);
                                $pt->setLogFile($log_file);

                                $paypalService = $this->getServiceLocator()->get('ErsBase\Service\PayPalService');
                                return $paypalService->testCredentials($pt, $err);
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
