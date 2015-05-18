<?php   

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Form;

use Zend\Form\Form;


class AcceptMatch extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('AcceptMatch');
        $this->setAttribute('method', 'post');
        $this->setAttribute('class', 'form-horizontal');
        
        /*$this->add(array(
            'name' => 'BankStatement_id',
            'attributes' => array(
                'type'  => 'hidden',
                'value_options' => array(
                    'foo',
                    'baz',
                    ),
            ),
        ));*/
        
        $this->add(array( 
            'name' => 'BankStatement_id', 
            'type' => 'Zend\Form\Element\MultiCheckbox', 
            'attributes' => array( 
                'required' => 'required', 
            ), 
            'options' => array( 
                'label' => 'Order',
                /*'value_options' => array(
                    '0' => 'Checkbox', 
                    '1' => 'Checkbox', 
                ),*/
            ), 
        ));
        
        /*$this->add(array(
            'name' => 'Order_id[]',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));*/
        
        $this->add(array( 
            'name' => 'Order_id', 
            'type' => 'Zend\Form\Element\MultiCheckbox', 
            'attributes' => array( 
                'required' => 'required', 
            ), 
            'options' => array( 
                'label' => 'Order',
                /*'value_options' => array(
                    '0' => 'Checkbox', 
                    '1' => 'Checkbox', 
                ),*/
            ), 
        ));
        
        $this->add(array(
            'name' => 'Admin_id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        
        $this->add(array( 
            'name' => 'comment', 
            'type' => 'Zend\Form\Element\Textarea', 
            'attributes' => array( 
                'required' => 'required', 
                'placeholder' => 'tell why this match is done',
                'class' => 'form-element form-control',
                'cols' => 70,
                'rows' => 5,
            ), 
            'options' => array( 
                'label' => 'Comment',
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
                'value' => 'Accept Match',
                'id' => 'submitbutton',
                'class' => 'btn btn-primary',
            ),
        ));
    }
}