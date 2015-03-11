<?php   

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Form;

use Zend\Form\Form;


class Deadline extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('Deadline');
        $this->setAttribute('method', 'post');
        
        $this->add(array(
            'name' => 'id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
 
        $this->add(array( 
            'name' => 'deadline', 
            'type' => 'Zend\Form\Element\DateTime', 
            'attributes' => array( 
                'placeholder' => 'Deadline...', 
                'required' => 'required',
                'class' => 'form-control form-element datetimepicker',
            ), 
            'options' => array( 
                'label' => 'Deadline', 
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ), 
        ));
        $this->get('deadline')->setFormat('Y-m-d H:i:s');
        
        $this->add(array(
            'type' => 'checkbox',
            'name' => 'priceChange',
            'attributes' => array(
                'class' => 'checkbox',
            ),
            'options' => array(
                'label' => 'This deadline can change prices',
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
                'value' => 'Go',
                'id' => 'submitbutton',
                'class' => 'btn btn-primary',
            ),
        ));
    }
}