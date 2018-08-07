<?php   

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Form;

use Zend\Form\Form;


class BankAccountFormat extends Form
{
    public function __construct()
    {
        parent::__construct('BankAccount');
        $this->setAttribute('method', 'post');
        
        $this->add(array(
            'name' => 'id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'matchKey',
            'attributes' => array(
                'class' => 'form-control form-element',
            ),
            'options' => array(
                'label' => 'contains order code',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'amount',
            'attributes' => array(
                'class' => 'form-control form-element',
            ),
            'options' => array(
                'label' => 'transfer amount',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
        $this->add(array(
            'name' => 'factor',
            'attributes' => array(
                'type'  => 'text',
                'class' => 'form-control form-element',
                'placeholder' => 'factor',
            ),
            'options' => array(
                'label' => 'factor to multiply with amount',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'name',
            'attributes' => array(
                'class' => 'form-control form-element',
            ),
            'options' => array(
                'label' => 'name of buyer',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'date',
            'attributes' => array(
                'class' => 'form-control form-element',
            ),
            'options' => array(
                'label' => 'date of transfer',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'sign',
            'attributes' => array(
                'class' => 'form-control form-element',
            ),
            'options' => array(
                'label' => 'contains algebraic sign (+ / -)',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
        
        $this->add(array(
            'name' => 'sign-value',
            'attributes' => array(
                'type'  => 'text',
                'class' => 'form-control form-element',
                'placeholder' => 'sign value',
            ),
            'options' => array(
                'label' => 'sign value for plus',
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
                'class' => 'btn btn-primary',
            ),
        ));
    }
}