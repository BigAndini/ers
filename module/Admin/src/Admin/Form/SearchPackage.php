<?php   

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Form;

use Zend\Form\Form;


class SearchPackage extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('SearchOrder');
        $this->setAttribute('method', 'get');
        
        /*$this->add(array(
            'type' => 'checkbox',
            'name' => 'paid',
            'attributes' => array(
                'class' => 'checkbox',
            ),
            'options' => array(
                'label' => 'Search only for paid orders',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
        
        $this->add(array(
            'type' => 'checkbox',
            'name' => 'match',
            'attributes' => array(
                'class' => 'checkbox',
            ),
            'options' => array(
                'label' => 'Search only for matched orders',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));*/
        
        $this->add(array(
            'name' => 'q',
            'attributes' => array(
                'type'  => 'text',
                'class' => 'form-control form-element',
                'placeholder' => 'code, first name, last name, birthdate, e-mail...',
            ),
            'options' => array(
                'label' => '',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
        
 
        /*$this->add(array( 
            'name' => 'csrf', 
            'type' => 'Zend\Form\Element\Csrf', 
        ));*/
        
        /*$this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Search',
                'id' => 'submitbutton',
                'class' => 'btn btn-primary',
            ),
        ));*/
    }
}