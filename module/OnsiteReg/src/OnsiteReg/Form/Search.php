<?php   

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace OnsiteReg\Form;

use Zend\Form\Form;


class Search extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('Search');
        $this->setAttribute('method', 'get');
        $this->setAttribute('class', 'form-inline form-group');
        
        $this->add(array(
            'name' => 'q',
            'attributes' => array(
                'required' => 'required',
                'type'  => 'text',
                'class' => 'form-control form-element',
                'style' => 'width: 100%;',
                'placeholder' => 'Code / Name / Birthdate...',
            ),
            'options' => array(
                'label' => '',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
        
        /*$this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Search',
                'class' => 'btn btn-lg btn-success',
            ),
        ));*/
    }
}