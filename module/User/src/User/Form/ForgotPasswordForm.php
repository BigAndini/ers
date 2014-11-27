<?php   

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace User\Form;

use Zend\Form\Form;


class ForgotPasswordForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('ForgotPassword');
        $this->setAttribute('method', 'post');
        
        $this->add(array(
            'name' => 'email',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'email',
            ),
        ));
        
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'reset password',
                'id' => 'submitbutton',
                'class' => 'btn btn-primary',
            ),
        ));
    }
}