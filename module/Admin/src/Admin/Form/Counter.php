<?php   

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Form;

use Zend\Form\Form;


class Counter extends Form
{
    public function __construct(Doctrine\ORM\EntityManager $em)
    {
        parent::__construct('Counter');
        $this->setAttribute('method', 'post');
        
        $this->add(array(
            'name' => 'id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        
        $this->add(array(
            'name' => 'name',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => 'Name ...',
                'required' => 'required',
                'class' => 'form-control form-element',
            ),
            'options' => array(
                'label' => 'Name',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
        
        $this->add(array(
            'name' => 'productVariantValue',
            'type'  => 'DoctrineModule\Form\Element\ObjectSelect',
            'attributes' => array(
                'required' => 'required', 
                'class' => 'form-control form-element',
            ),
            'options' => array(
                'label' => 'Product variant',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
                'object_manager' => $em,
                'target_class' => 'ErsBase\Entity\ProductVariantValue',
                'label_generator' => function($entity){ return $entity->getProductVariant()->getName() . ' - ' . $entity->getValue(); },
                'display_empty_item' => true,
                'empty_item_label' => 'Select variant ...',
            ),
        ));
        
        $this->add(array(
            'name' => 'value',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => 'Value ...',
                'required' => 'required',
                'pattern' => '\d+',
                'class' => 'form-control form-element',
            ),
            'options' => array(
                'label' => 'Counter value',
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