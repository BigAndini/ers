<?php   

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Form;

use Zend\Form\Form;


class ProductPackage extends Form
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
            'name' => 'product_id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        
        $this->add(array(
            'name' => 'sub_product_id',
            'type'  => 'Zend\Form\Element\Select',
            'attributes' => array(
                'required' => 'required', 
                'class' => 'form-control form-element',
            ),
            'options' => array(
                'label' => 'Sub Product',
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
            'name' => 'amount',
            'attributes' => array(
                'type'  => 'text',
                'class' => 'form-control form-element',
                'placeholder' => 'Amount...',
                'value' => '1',
            ),
            'options' => array(
                'label' => 'Amount',
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