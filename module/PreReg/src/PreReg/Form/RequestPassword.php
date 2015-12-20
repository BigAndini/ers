<?php   

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreReg\Form;

use Zend\Form\Form;

class RequestPassword extends Form
{
    public $inputFilter;
    
    public function __construct($name = null)
    {
        parent::__construct('RequestPassword');
        
        $this->setAttribute('method', 'post'); 
        
        $this->add(array( 
            'name' => 'email', 
            'type' => 'Zend\Form\Element\Email', 
            'attributes' => array( 
                'placeholder' => 'Email Address...', 
                #'required' => 'required', 
                'class' => 'form-control form-element',
            ), 
            'options' => array( 
                'label' => 'Email', 
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
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
                'value' => 'Send',
                'id' => 'submitbutton',
                'class' => 'btn btn-primary',
            ),
        ));
    }
    
}