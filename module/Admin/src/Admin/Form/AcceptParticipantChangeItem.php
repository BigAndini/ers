<?php   

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Form;

use Zend\Form\Form;


class AcceptParticipantChangeItem extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('AcceptParticipantChangeItem');
        $this->setAttribute('method', 'post');
        $this->setAttribute('class', 'form-horizontal');
        
        $this->add(array(
            'name' => 'item_id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        $this->add(array(
            'name' => 'user_id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        
        $this->add(array( 
            'name' => 'comment', 
            'type' => 'Zend\Form\Element\Textarea', 
            'attributes' => array( 
                'required' => 'required', 
                'placeholder' => 'tell why this transfer is done',
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
                'value' => 'accept transfer',
                'id' => 'submitbutton',
                'class' => 'btn btn-success',
            ),
        ));
    }
}