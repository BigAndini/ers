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


class PaymentType extends Form
{
    public $inputFilter;
    
    public function __construct($name = null)
    {
        parent::__construct('PaymentType');
        
        $this->setAttribute('method', 'post'); 
        
        $this->add(array( 
            'name' => 'paymenttype_id', 
            #'disable_inarray_validator' => false,
            #'disable_inarray_validator' => true,
            'type' => 'Zend\Form\Element\Radio', 
            'attributes' => array( 
                #'required' => 'required',
                'class' => 'checkbox-inline',
            ), 
            'options' => array( 
                'label' => 'Choose Payment type', 
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
                'value' => 'Save & Continue',
                'id' => 'submitbutton',
                'class' => 'btn btn-success btn-lg',
            ),
        ));
    }
    
}