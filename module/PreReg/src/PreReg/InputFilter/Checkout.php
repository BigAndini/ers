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

use Zend\Validator;

class Checkout implements InputFilterAwareInterface 
{ 
    protected $inputFilter; 
    
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
                'name' => 'terms', 
                'required' => true, 
                'filters' => array( 
                    array('name' => 'Int'), 
                ),
                'validators' => array(
                    array(
                        'name' => 'Digits',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'messages' => array(
                                Validator\Digits::NOT_DIGITS => 'You must agree to the terms and conditions.',
                            ),
                        ),
                    ),
                ),
            ]));
            
            $this->inputFilter = $inputFilter; 
        } 
        
        return $this->inputFilter; 
    } 
} 