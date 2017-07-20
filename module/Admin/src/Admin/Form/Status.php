<?php   

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Form;

use Zend\Form\Form;

class Status extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('Status');
        $this->setAttribute('method', 'post');
        $this->add(array(
            'name' => 'id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        $this->add(array(
            'name' => 'position',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => 'Position...',
                'class' => 'form-control form-element',
            ),
            'options' => array(
                'label' => 'Position',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
        $this->add(array(
            'name' => 'value',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => 'Value...',
                'class' => 'form-control form-element',
            ),
            'options' => array(
                'label' => 'Value',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
        $this->add(array(
            'type' => 'checkbox',
            'name' => 'valid',
            'attributes' => array(
                'class' => 'checkbox',
                'value' => '0',
            ),
            'options' => array(
                'label' => 'Valid (System treats this status to send out e-tickets)',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
        $this->add(array(
            'type' => 'checkbox',
            'name' => 'active',
            'attributes' => array(
                'class' => 'checkbox',
                'value' => '1',
            ),
            'options' => array(
                'label' => 'Active',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
        $this->add(array(
            'name' => 'description',
            'attributes' => array(
                'type'  => 'int',
                'placeholder' => 'Description...',
                'class' => 'form-control form-element',
            ),
            'options' => array(
                'label' => 'Description',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
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