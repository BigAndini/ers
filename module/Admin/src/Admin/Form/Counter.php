<?php   

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Form;

use Zend\Form\Form;
use Doctrine\ORM\EntityManager;
use Zend\InputFilter\InputFilterProviderInterface;

class Counter extends Form implements InputFilterProviderInterface
{
    public function __construct(EntityManager $em)
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
                #'required' => 'required', 
                'class' => 'form-control form-element',
            ),
            'options' => array(
                'label' => 'product variant value',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
                'object_manager' => $em,
                'target_class' => 'ErsBase\Entity\ProductVariantValue',
                'label_generator' => function($entity){ return $entity->getProductVariant()->getProduct()->getName() . ' - ' . $entity->getProductVariant()->getName() . ' - ' . $entity->getValue(); },
                'display_empty_item' => true,
                'empty_item_label' => 'select product variant value ...',
            ),
        ));
                
        $this->add(array(
            'name' => 'productVariant',
            'type'  => 'DoctrineModule\Form\Element\ObjectSelect',
            'attributes' => array(
                #'required' => 'required', 
                'class' => 'form-control form-element',
            ),
            'options' => array(
                'label' => 'product variant',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
                'object_manager' => $em,
                'target_class' => 'ErsBase\Entity\ProductVariant',
                'label_generator' => function($entity){ return $entity->getProduct()->getName() . ' - ' . $entity->getName(); },
                'display_empty_item' => true,
                'empty_item_label' => 'select product variant ...',
            ),
        ));
                
        $this->add(array(
            'name' => 'product',
            'type'  => 'DoctrineModule\Form\Element\ObjectSelect',
            'attributes' => array(
                #'required' => 'required', 
                'class' => 'form-control form-element',
            ),
            'options' => array(
                'label' => 'product',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
                'object_manager' => $em,
                'target_class' => 'ErsBase\Entity\Product',
                'label_generator' => function($entity){ return $entity->getName(); },
                'display_empty_item' => true,
                'empty_item_label' => 'select product ...',
            ),
        ));
        
        $this->add(array(
            'name' => 'value',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => 'Limit ...',
                'required' => 'required',
                'pattern' => '\d+',
                'class' => 'form-control form-element',
            ),
            'options' => array(
                'label' => 'Counter limit',
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
    
    /**
     * Should return an array specification compatible with
     * {@link Zend\InputFilter\Factory::createInputFilter()}.
     *
     * @return array
     */
    public function getInputFilterSpecification()
    {
        return array(
            'id' => array(
                'required' => false,
                'filters' => array(
                ),
                'validators' => array(
                    
                ),
            ),
            'name' => array(
                'required' => false,
                'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    
                ),
            ),
            'productVariantValue' => array(
                'required' => false,
                'filters' => array(
                ),
                'validators' => array(
                    
                ),
            ),
            'productVariant' => array(
                'required' => false,
                'filters' => array(
                ),
                'validators' => array(
                    
                ),
            ),
            'product' => array(
                'required' => false,
                'filters' => array(
                ),
                'validators' => array(
                    
                ),
            ),
            'name' => array(
                'required' => false,
                'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    
                ),
            ),
        );
    }
}