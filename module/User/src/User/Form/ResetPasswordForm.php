<?php   

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace User\Form;

use Zend\Form\Form;


class ResetPasswordForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('ResetPassword');
        $this->setAttribute('method', 'post');
        
        $this->add(array(
            'name' => 'password1',
            'attributes' => array(
                'type'  => 'password',
            ),
            'options' => array(
                'label' => 'new password',
            ),
        ));
        $this->add(array(
            'name' => 'password2',
            'attributes' => array(
                'type'  => 'password',
            ),
            'options' => array(
                'label' => 'confirm password',
            ),
        ));
        
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'save',
                'id' => 'submitbutton',
                'class' => 'btn btn-primary',
            ),
        ));
    }
}