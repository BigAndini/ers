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


class PurchaserForm extends Form
{
    public $inputFilter;
    
    public function __construct($name = null)
    {
        parent::__construct('Purchaser');
        
        $this->setAttribute('method', 'post'); 
        
        
        $this->add(array( 
            'name' => 'purchaser', 
            'type' => 'Zend\Form\Element\Radio', 
            'attributes' => array( 
                'required' => 'required',
            ), 
            'options' => array( 
                'label' => 'Choose Purchaser', 
                /*'value_options' => array(
                    '1' => 'foobar', 
                    '0' => 'Add a Purchaser', 
                ),*/
            ), 
        ));
        
        $this->add(array( 
            'name' => 'prename', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'placeholder' => 'Prename...', 
                'required' => 'required', 
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
                'required' => 'required', 
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
                'required' => 'required', 
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
    
    public function getInputFilter()
    {
        if (!$this->inputFilter) 
        { 
            $inputFilter = new InputFilter(); 
            $factory = new InputFactory();             
            
            $inputFilter->add($factory->createInput([ 
                'name' => 'prename', 
                'required' => true, 
                'filters' => array( 
                    array('name' => 'StripTags'), 
                    array('name' => 'StringTrim'), 
                ), 
                'validators' => array(
                    array(
                        'name' => 'Callback',
                        'options' => array(
                            'messages' => array(
                                \Zend\Validator\Callback::INVALID_VALUE => 'The Prename cannot be empty.',
                            ),
                            'callback' => function($value, $context=array()) {
                                /* 
                                 * If no participant and not even the add 
                                 * purchaser is chosen the form is invalid.
                                 */
                                if(!isset($context['participant'])) {
                                    return false;
                                }
                                /*
                                 * If the participant is not 0 the user adds an 
                                 * already existing participant as purchaser.
                                 */
                                if($context['participant'] != 0) {
                                    return true;
                                }
                                
                                if(strlen($value) == 0) {
                                    return false;
                                }
                            },
                            
                        ),
                    ),
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 0,
                            'max'      => 100,
                        ),
                    ),
                ), 
            ])); 

            $inputFilter->add($factory->createInput([ 
                'name' => 'surname', 
                'required' => true, 
                'filters' => array( 
                    array('name' => 'StripTags'), 
                    array('name' => 'StringTrim'), 
                ), 
                'validators' => array( 
                ), 
            ])); 

            $inputFilter->add($factory->createInput([ 
                'name' => 'birthday', 
                'required' => true, 
                'filters' => array( 
                    array('name' => 'StripTags'), 
                    array('name' => 'StringTrim'), 
                ), 
                'validators' => array( 
                    array(
                        'name' => 'Between',
                        'options' => array(
                        ),
                    ),
                ), 
            ])); 

            $inputFilter->add($factory->createInput([ 
                'name' => 'email', 
                'filters' => array( 
                    array('name' => 'StripTags'), 
                    array('name' => 'StringTrim'), 
                ), 
                'validators' => array( 
                    array ( 
                        'name' => 'EmailAddress', 
                        'options' => array( 
                            'messages' => array( 
                                'emailAddressInvalidFormat' => 'Email address format is not invalid', 
                            ) 
                        ), 
                    ), 
                    array ( 
                        'name' => 'NotEmpty', 
                        'options' => array( 
                            'messages' => array( 
                                'isEmpty' => '', 
                            ) 
                        ), 
                    ), 
                ), 
            ])); 
 
            $this->inputFilter = $inputFilter; 
        } 
        
        return $this->inputFilter; 
    }
}