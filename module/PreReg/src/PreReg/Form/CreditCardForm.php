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


class CreditCardForm extends Form
{
    public $inputFilter;
    
    public function __construct($name = null)
    {
        parent::__construct('CreditCard');
        
        $this->setAttribute('method', 'post'); 
        
        $formElement = array();
        $formElement['name'] = 'cc_typ';
        $formElement['type'] = 'Zend\Form\Element\Select';

        $formElement['options'] = array();
        $formElement['options']['label'] = 'Type of credit card';
        $this->add($formElement);
        
        $this->add(array( 
            'name' => 'prename', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'placeholder' => 'Name of holder...', 
                'required' => 'required', 
            ), 
            'options' => array( 
                'label' => 'Name of Holder', 
            ), 
        )); 
 
        $this->add(array( 
            'name' => 'surname', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'placeholder' => 'Credit Card Number...', 
                'required' => 'required', 
            ), 
            'options' => array( 
                'label' => 'Credit Card Number', 
            ), 
        ));
        
        $this->add(array( 
            'name' => 'surname', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'placeholder' => '3 digit check number...', 
                'required' => 'required', 
            ), 
            'options' => array( 
                'label' => '3 digit check number', 
            ), 
        ));
        
        $this->add(array( 
            'name' => 'cc_expdate_month', 
            'type' => 'Zend\Form\Element\Select', 
            'attributes' => array( 
                'required' => 'required', 
            ),
            'options' => array(
                '1' => '01',
                '2' => '02',
                '3' => '03',
                '4' => '04',
                '5' => '05',
                '6' => '06',
                '7' => '07',
                '8' => '08',
                '9' => '09',
                '10' => '10',
                '11' => '11',
                '12' => '12',
            ),
            'options' => array( 
                'label' => 'Expire Month', 
            ), 
        ));
 
        $this->add(array( 
            'name' => 'cc_expdate_year', 
            'type' => 'Zend\Form\Element\Select', 
            'attributes' => array( 
                'required' => 'required', 
            ),
            'options' => array(
                '2015' => '2015',
                '2016' => '2016',
                '2017' => '2017',
                '2018' => '2018',
                '2019' => '2019',
                '2020' => '2020',
            ),
            'options' => array( 
                'label' => 'Expire Year', 
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
                'value' => 'pay now',
                'id' => 'submitbutton',
                'class' => 'btn btn-primary',
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