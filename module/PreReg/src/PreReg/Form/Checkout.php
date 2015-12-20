<?php   

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreReg\Form;

use Zend\Form\Form;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;


class Checkout extends Form
{
    public $inputFilter;
    
    public function __construct($name = null)
    {
        parent::__construct('Buyer');
        
        $this->setAttribute('method', 'post'); 
        
        $this->add(array( 
            'name' => 'terms', 
            'type' => 'Zend\Form\Element\Checkbox', 
            'attributes' => array( 
                'required' => 'required', 
                'value' => '0', 
                'class' => 'checkbox-inline',
            ), 
            'options' => array( 
                'label' => 'I agree to all terms and conditions',
                'use_hidden_element' => true,
                'checked_value' => 1,
                'unchecked_value' => 'no',
            ), 
        )); 
        
        $this->add(array( 
            'name' => 'csrf', 
            'type' => 'Zend\Form\Element\Csrf', 
        )); 
        
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Buy Now',
                'id' => 'submitbutton',
                'class' => 'btn btn-primary btn-lg',
            ),
        ));
    }
    
}