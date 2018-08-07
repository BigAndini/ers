<?php   

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Form;

#use Zend\Form\Form;
#use Application\Entity\Product;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Stdlib\Hydrator\ClassMethods as ClassMethodsHydrator;

class ProductVariantFieldset extends Fieldset implements InputFilterProviderInterface
{
    public function __construct()
    {
        parent::__construct('ProductVariantFieldset');
        
        $this
            ->setHydrator(new ClassMethodsHydrator(false))
            ->setObject(new Category());

        $this->setLabel('Product Variant');
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'variant',
            'attributes' => array(
                'class' => 'form-control form-element',
            ),
            'options' => array(
                'label' => 'Product Variant',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ),
        ));
    }
}