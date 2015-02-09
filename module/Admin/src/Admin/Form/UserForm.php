<?php   

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Form;

use Zend\Form\Form;


class UserForm extends Form
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
            'name' => 'email', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'placeholder' => 'E-Mail Address...', 
                'required' => 'required', 
            ), 
            'options' => array( 
                'label' => 'E-Mail Address', 
            ), 
        )); 
 
        $this->add(array( 
            'name' => 'prename', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'placeholder' => 'Prename...', 
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
            ), 
            'options' => array( 
                'label' => 'Surname', 
            ), 
        )); 
 
        $this->add(array( 
            'name' => 'roles', 
            'type' => 'Zend\Form\Element\MultiCheckbox', 
            /*'attributes' => array(
                'required' => 'required',
            ),*/ 
            'options' => array( 
                'label' => 'Roles', 
                'value_options' => array(
                    /*array(
                        'value' => '0',
                        'label' => 'user',
                        'selected' => false,
                        'disabled' => false,
                    ),
                    array(
                        'value' => '1',
                        'label' => 'admin',
                        'selected' => false,
                        'disabled' => false,
                    ),
                    array(
                        'value' => '2',
                        'label' => 'participant',
                        'selected' => true,
                        'disabled' => false,
                    ),*/
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