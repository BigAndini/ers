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


class Participant extends Form
{
    public $inputFilter;
    
    public function __construct($name = null)
    {
        parent::__construct('Participant');
        
        $this->setAttribute('method', 'post'); 
        
        $this->add(array(
            'name' => 'session_id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        
        $this->add(array( 
            'name' => 'firstname', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'placeholder' => 'First name...', 
                'required' => 'required', 
                'class' => 'form-control form-element',
            ), 
            'options' => array( 
                'label' => 'First name',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ), 
        )); 
 
        $this->add(array( 
            'name' => 'surname', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'placeholder' => 'Surname...', 
                'required' => 'required', 
                'class' => 'form-control form-element',
            ), 
            'options' => array( 
                'label' => 'Surname',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ), 
        )); 
 
        $this->add(array( 
            'name' => 'birthday', 
            #'type' => 'Zend\Form\Element\Date',
            #'type' => 'Zend\Form\Element\Text',
            'type' => 'PreReg\Form\Element\DateText',
            'attributes' => array( 
                'placeholder' => 'Birthday...', 
                'required' => 'required',
                'class' => 'form-control form-element datepicker',
                #'min' => '1900-01-01', 
                #'max' => 2015-08-09, 
                #'step' => '1', 
            ), 
            'options' => array( 
                'label' => 'Date of birth',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ), 
        ));
        #$this->get('birthday')->setFormat('Y-m-d');
        $this->get('birthday')->setFormat('d.m.Y');
 
        $this->add(array( 
            'name' => 'email', 
            'type' => 'Zend\Form\Element\Email', 
            'attributes' => array( 
                'placeholder' => 'Email Address...', 
                #'required' => 'required', 
                'class' => 'form-control form-element',
            ), 
            'options' => array( 
                'label' => 'Email (optional)', 
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ), 
        )); 
        
         $this->add(array(
            'name' => 'Country_id',
            'type'  => 'Zend\Form\Element\Select',
            'attributes' => array(
                'required' => 'required',
                'class' => 'form-control form-element',
            ),
            'options' => array(
                'label' => 'Where are you from?',
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
                'value' => 'Save',
                'id' => 'submitbutton',
                'class' => 'btn btn-success',
            ),
        ));
    }
}