<?php   

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Form;

use Zend\Form\Form;


class User extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('User');
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
                'placeholder' => 'Firstname...', 
                'class' => 'form-control form-element',
            ), 
            'options' => array( 
                'label' => 'Firstname',
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
            'name' => 'email', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'placeholder' => 'E-Mail Address...', 
                'class' => 'form-control form-element',
            ), 
            'options' => array( 
                'label' => 'E-Mail Address', 
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
        $this->get('birthday')->setFormat('d.m.Y');
 
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
                'value' => 'Go',
                'id' => 'submitbutton',
                'class' => 'btn btn-primary',
            ),
        ));
    }
}