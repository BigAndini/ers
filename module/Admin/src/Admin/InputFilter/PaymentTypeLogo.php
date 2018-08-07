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

class PaymentTypeLogo implements InputFilterAwareInterface 
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
                'name' => 'logo-upload', 
                'filters' => array( 
                    array('name' => 'StripTags'), 
                    array('name' => 'StringTrim'), 
                ), 
                'validators' => array( 
                    array ( 
                        'name' => 'Size', 
                        'options' => array( 
                            'max' => '1MB', 
                        ), 
                    ), 
                    array ( 
                        'name' => 'Count', 
                        'options' => array( 
                            'min' => '0', 
                            'max' => '1',
                        ), 
                    ), 
                    array ( 
                        'name' => 'Extension', 
                        'options' => array( 
                            jpg, jpeg, png, 
                        ), 
                    ), 
                    array ( 
                        'name' => 'MimeType', 
                        'options' => array( 
                            image, 
                        ), 
                    ), 
                ), 
            ])); 

            $this->inputFilter = $inputFilter; 
        } 
        
        return $this->inputFilter; 
    } 
} 