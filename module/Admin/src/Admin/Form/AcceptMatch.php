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
    public function __construct()
    {
        parent::__construct('AcceptMatch');
        $this->setAttribute('method', 'post');
        $this->setAttribute('class', 'form-horizontal');
        
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
            'name' => 'half-match',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Match -> unpaid',
                'id' => 'submitbutton',
                'class' => 'btn btn-warning',
            ),
        ));
        
        $this->add(array(
            'name' => 'full-match',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Match -> paid',
                'id' => 'submitbutton',
                'class' => 'btn btn-success',
            ),
        ));
    }
}