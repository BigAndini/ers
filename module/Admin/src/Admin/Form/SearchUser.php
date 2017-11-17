<?php   

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Form;

use Zend\Form\Form;


class SearchUser extends Form
{
    public function __construct()
    {
        parent::__construct('SearchOrder');
        $this->setAttribute('method', 'get');
        
        $this->add(array(
            'name' => 'q',
            'attributes' => array(
                'type'  => 'text',
                'class' => 'form-control form-element',
                'placeholder' => 'first name, last name, birthdate, e-mail...',
            ),
            'options' => array(
                'label' => '',
                'label_attributes' => array(
                    'class'  => 'media-object input-group-lg',
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
                'value' => 'Search',
                'id' => 'submitbutton',
                'class' => 'btn btn-primary',
            ),
        ));
    }
}