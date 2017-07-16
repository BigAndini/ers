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


class User extends Form
{
    public $inputFilter;
    
    public function __construct($name = null)
    {
        parent::__construct('Participant');
        
        $this->setAttribute('method', 'post'); 
        
        $this->add(array(
            'name' => 'id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        
        $this->add(array( 
            'name' => 'firstname', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'placeholder' => _('Firstname...'), 
                'required' => 'required', 
                'class' => 'form-control form-element',
            ), 
            'options' => array( 
                'label' => _('Firstname'), 
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ), 
        )); 
 
        $this->add(array( 
            'name' => 'surname', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'placeholder' => _('Surname...'), 
                'required' => 'required', 
                'class' => 'form-control form-element',
            ), 
            'options' => array( 
                'label' => _('Surname'),
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ), 
        )); 
 
        $this->add(array( 
            'name' => 'birthday', 
            'type' => 'PreReg\Form\Element\DateText',
            'attributes' => array( 
                'placeholder' => _('Birthday...'), 
                'class' => 'form-control form-element datepicker',
            ), 
            'options' => array( 
                'label' => _('Birthday'), 
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
                'format' => 'Y-m-d',
            ), 
        ));
        $this->get('birthday')->setFormat('Y-m-d');
 
        $this->add(array( 
            'name' => 'email', 
            'type' => 'Zend\Form\Element\Email', 
            'attributes' => array( 
                'placeholder' => _('Email Address...'), 
                'class' => 'form-control form-element',
            ), 
            'options' => array( 
                'label' => _('Email'), 
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
                'value' => _('Ok'),
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
                'name' => 'firstname', 
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
                'required' => false, 
                'filters' => array( 
                    array('name' => 'StripTags'), 
                    array('name' => 'StringTrim'), 
                ), 
                'validators' => array( 
                    array ( 
                        'name' => 'EmailAddress', 
                        'options' => array( 
                            'messages' => array( 
                                'emailAddressInvalidFormat' => _('Email address format is not invalid'), 
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