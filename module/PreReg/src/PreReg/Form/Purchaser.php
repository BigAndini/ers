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


class Purchaser extends Form
{
    public $inputFilter;
    
    public function __construct($name = null)
    {
        parent::__construct('Purchaser');
        
        $this->setAttribute('method', 'post'); 
        
        $this->add(array( 
            'name' => 'purchaser_id', 
            #'disable_inarray_validator' => false,
            'type' => 'Zend\Form\Element\Radio', 
            'attributes' => array( 
                'required' => 'required',
            ), 
            'options' => array( 
                'label' => 'Choose Purchaser', 
            ), 
        ));
        
        $this->add(array( 
            'name' => 'prename', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'placeholder' => 'Prename...',
                'class' => 'form-control form-element',
            ), 
            'options' => array( 
                'label' => 'Prename', 
            ), 
        ));
 
        $this->add(array( 
            'name' => 'surname', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'placeholder' => 'Surname...',
                'class' => 'form-control form-element',
            ), 
            'options' => array( 
                'label' => 'Surname', 
            ), 
        ));
 
        $this->add(array( 
            'name' => 'email', 
            'type' => 'Zend\Form\Element\Email', 
            'attributes' => array( 
                'placeholder' => 'Email Address...', 
                'class' => 'form-control form-element',
            ), 
            'options' => array( 
                'label' => 'Email', 
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
                'value' => 'Save Purchaser',
                'id' => 'submitbutton',
                'class' => 'btn btn-primary btn-lg',
            ),
        ));
    }
    
}