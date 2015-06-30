<?php   

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Form;

use Zend\Form\Form;


class EnterRefund extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('Product');
        $this->setAttribute('method', 'post');
        
        $this->add(array(
            'name' => 'id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        $this->add(array(
            'name' => 'amount',
            'attributes' => array(
                'type'  => 'text',
                'class' => 'form-control form-element',
                'placeholder' => 'Amount...',
            ),
            'options' => array(
                'label' => 'Amount',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
        
        /*$this->add(array( 
            'name' => 'comment', 
            'type' => 'Zend\Form\Element\Textarea', 
            'attributes' => array( 
                'required' => 'required', 
                'placeholder' => 'tell why this refund is done',
                'class' => 'form-element form-control',
                'cols' => 70,
                'rows' => 5,
            ), 
            'options' => array( 
                'label' => 'Comment',
            ), 
        ));*/
        
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