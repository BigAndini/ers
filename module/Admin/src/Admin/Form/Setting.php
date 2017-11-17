<?php   

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Form;

use Zend\Form\Form;


class Setting extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('Agegroup');
        $this->setAttribute('method', 'post');
        
        $this->add(array(
            'name' => 'id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        
        $this->add(array(
            'name' => 'key',
            'attributes' => array(
                'required' => 'required',
                'type'  => 'text',
                'class' => 'form-control form-element',
                'placeholder' => 'key...',
            ),
            'options' => array(
                'label' => 'key',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
        
        $this->add(array(
            'name' => 'value',
            'attributes' => array(
                'required' => 'required',
                'type'  => 'text',
                'class' => 'form-control form-element',
                'placeholder' => 'value...',
            ),
            'options' => array(
                'label' => 'value',
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
                'value' => 'Save',
                'id' => 'submitbutton',
                'class' => 'btn btn-primary',
            ),
        ));
    }
}