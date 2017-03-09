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


class ChangePassword extends Form
{
    public $inputFilter;
    
    public function __construct($name = null)
    {
        parent::__construct('Participant');
        
        $this->setAttribute('method', 'post'); 
        
        $this->add(array(
            'name' => 'email',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        
        $this->add(array( 
            'name' => 'oldpassword', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'placeholder' => _('old password...'), 
                'required' => 'required', 
                'class' => 'form-control form-element',
            ), 
            'options' => array( 
                'label' => _('Old Password'), 
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ), 
        )); 
 
        $this->add(array( 
            'name' => 'newpassword', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'placeholder' => _('new password...'), 
                'required' => 'required', 
                'class' => 'form-control form-element',
            ), 
            'options' => array( 
                'label' => _('New Password'), 
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ), 
        )); 
 
        $this->add(array( 
            'name' => 'confirmpassword', 
            'type' => 'Zend\Form\Element\Password', 
            'attributes' => array( 
                'placeholder' => _('confirm password...'), 
                'required' => 'required', 
                'class' => 'form-control form-element',
            ), 
            'options' => array( 
                'label' => _('Confirm Password'), 
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
                'value' => _('Save'),
                'id' => 'submitbutton',
                'class' => 'btn btn-primary',
            ),
        ));
    }
}