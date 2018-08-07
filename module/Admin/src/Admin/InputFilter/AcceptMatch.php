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

class AcceptMatch implements InputFilterAwareInterface 
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
                'name' => 'BankStatement_id', 
                'required' => true, 
                'filters' => array( 
                    array("name" => "Callback", "options" => array(
                        "callback" => function($values) {
                            $strip = new \Zend\Filter\StripTags();
                            $trim = new \Zend\Filter\StringTrim();
                            $int = new \Zend\Filter\Int();
                            foreach($values as $key => $val) {
                                $val = $strip->filter($val);
                                $val = $trim->filter($val);
                                $val = $int->filter($val);
                                $values[$key] = $val;
                            }
                            return $values;
                        }),
                    ),
                ), 
                'validators' => array(
                    array ( 
                        'name' => 'Callback', 
                        'options' => array(
                            'messages' => array(
                                \Zend\Validator\Callback::INVALID_VALUE => 'Please choose either one statement and multiple orders or multiple statements and one order.',
                            ),
                            'callback' => function($values, $context=array()) {
                                if(count($values) == 1 && count($context['Order_id']) >= 1) {
                                    return true;
                                }
                                if(count($values) >= 1 && count($context['Order_id']) == 1) {
                                    return true;
                                }
                                return false;
                            },
                            
                        ),
                    ),
                ), 
            ])); 

            $inputFilter->add($factory->createInput([ 
                'name' => 'Order_id', 
                'required' => true, 
                'filters' => array( 
                    array("name" => "Callback", "options" => array(
                        "callback" => function($values) {
                            $strip = new \Zend\Filter\StripTags();
                            $trim = new \Zend\Filter\StringTrim();
                            $int = new \Zend\Filter\Int();
                            foreach($values as $key => $val) {
                                $val = $strip->filter($val);
                                $val = $trim->filter($val);
                                $val = $int->filter($val);
                                $values[$key] = $val;
                            }
                            return $values;
                        }),
                    ),
                ), 
                'validators' => array(
                    array ( 
                        'name' => 'Callback', 
                        'options' => array(
                            'messages' => array(
                                \Zend\Validator\Callback::INVALID_VALUE => 'Please choose either one order and multiple statements or multiple orders and one statement.',
                            ),
                            'callback' => function($values, $context=array()) {
                                if(count($values) == 1 && count($context['BankStatement_id']) >= 1) {
                                    return true;
                                }
                                if(count($values) >= 1 && count($context['BankStatement_id']) == 1) {
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