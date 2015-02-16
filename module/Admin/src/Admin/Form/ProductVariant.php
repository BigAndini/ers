<?php   

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Form;

use Zend\Form\Form;


class ProductVariant extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('ProductVariant');

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
            'type' => 'Zend\Form\Element\Select',
            'name' => 'type',
            'options' => array(
                'label' => 'Type',
            ),
        ));
        
        $this->add(array(
            'name' => 'preselection',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Preselection',
            ),
        ));
        
        $this->add(array(
            'name' => 'Product_id',
            'attributes' => array(
                'type'  => 'hidden',
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