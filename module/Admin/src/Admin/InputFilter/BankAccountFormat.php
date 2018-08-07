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
use Zend\Session\Container;

class BankAccountFormat implements InputFilterAwareInterface 
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
                'name' => 'amount', 
                'required' => true, 
                'filters' => array( 
                    array('name' => 'Int'), 
                ), 
                'validators' => array(
                ), 
            ])); 
            $inputFilter->add($factory->createInput([ 
                'name' => 'name', 
                'required' => true, 
                'filters' => array( 
                    array('name' => 'Int'), 
                ), 
                'validators' => array(
                ), 
            ])); 
            $inputFilter->add($factory->createInput([ 
                'name' => 'date', 
                'required' => true, 
                'filters' => array( 
                    array('name' => 'Int'), 
                ), 
                'validators' => array(
                ), 
            ])); 
            $inputFilter->add($factory->createInput([ 
                'name' => 'matchKey', 
                'required' => true, 
                'filters' => array( 
                    array('name' => 'Int'), 
                ), 
                'validators' => array(
                ), 
            ])); 
 
            $this->inputFilter = $inputFilter; 
        } 
        
        return $this->inputFilter; 
    }
} 