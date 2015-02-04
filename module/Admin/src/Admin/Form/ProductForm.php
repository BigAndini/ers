<?php   

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Form;

use Zend\Form\Form;


class ProductForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('Product');
        $this->setAttribute('method', 'post');
        
        $this->add(array(
            'name' => 'id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        $this->add(array(
            'name' => 'ordering',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Order',
            ),
        ));
        $this->add(array(
            'name' => 'name',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Name',
            ),
        ));
        $this->add(array(
            'name' => 'shortDescription',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Short Description',
            ),
        ));
        $this->add(array(
            'name' => 'longDescription',
            'attributes' => array(
                'type'  => 'textarea',
            ),
            'options' => array(
                'label' => 'Long Description',
            ),
        ));
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'taxId',
            'options' => array(
                    'label' => 'Tax Group',
            ),
        ));
        
        $this->add(array(
            'type' => 'checkbox',
            'name' => 'personalized',
            'options' => array(
                    'label' => 'Personalized',
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