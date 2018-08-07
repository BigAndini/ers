<?php   

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreReg\Form;

use Zend\Form\Form;

class ResetPassword extends Form
{
    public $inputFilter;
    
    public function __construct()
    {
        parent::__construct('ResetPassword');
        
        $this->setAttribute('method', 'post'); 
        
        $this->add(array( 
            'name' => 'newPassword', 
            'type' => 'Zend\Form\Element\Password', 
            'attributes' => array( 
                'placeholder' => _('your password...'), 
                'required' => 'required', 
                'class' => 'form-control form-element',
            ), 
            'options' => array( 
                'label' => _('new password'), 
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ), 
        )); 
 
        $this->add(array( 
            'name' => 'newPassword2', 
            'type' => 'Zend\Form\Element\Password', 
            'attributes' => array( 
                'placeholder' => _('confirm password...'), 
                'required' => 'required', 
                'class' => 'form-control form-element',
            ), 
            'options' => array( 
                'label' => _('confirm password'), 
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