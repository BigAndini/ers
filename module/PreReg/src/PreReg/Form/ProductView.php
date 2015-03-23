<?php   

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreReg\Form;

use Zend\Form\Form;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use ersEntity\Entity;

class ProductView extends Form
{
    protected $sl;
    
    public function setServiceLocator($sl) {
        $this->sl = $sl;
    }
    
    public function getServiceLocator() {
        if(!isset($this->sl)) {
            throw new \Exception('Unable to find ServiceLocator');
        }
        return $this->sl;
    }
    
    public function __construct($name = null)
    {
        
        parent::__construct('Product');
        #$this->variantCounter = 0;
        $this->variants = array();
        $this->setAttribute('method', 'post');
        
        $this->add(array(
            'name' => 'id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        $this->add(array(
            'name' => 'Product_id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        $this->add(array(
            'name' => 'Price_id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        
        $this->add(array(
            'name' => 'participant_id',
            'type'  => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control form-element',
            ),
            'options' => array(
                'label' => 'This ticket belongs to:',
                'label_attributes' => array(
                    'class'  => 'media-object',
                    'id' => 'participant',
                ),
            ),
        ));
        $this->add(array(
            'name' => 'agegroup_id',
            'require' => true,
            'type'  => 'Zend\Form\Element\Radio',
            'attributes' => array(
                'class' => 'checkbox-inline',
            ),
            'options' => array(
                'label' => 'Agegroup',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
                'legend_attributes' => array(
                    'class' => 'hide',
                ),
            ),
        ));
        
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Go',
                'id' => 'submitbutton',
                'class' => 'btn btn-success',
            ),
        ));
        
        $this->getInputFilter();
    }

    public function setVariants($variants, $defaults = array()) {
        $variant_count = count($variants);
        $variant_add = 1;
        foreach($variants as $v) {
            if(is_object($v) && $v instanceof Entity\ProductVariant) {
                if($v->getOrder() != null && $v->getOrder() != 0) {
                    $this->variants[$v->getOrder()] = $v;
                } else {
                    # Make sure the variants without order number or 
                    # order number == 0 will be shown last.
                    $this->variants[$variant_count+$variant_add] = $v;
                    $variant_add++;
                }
            } else {
                $logger = $this
                    ->getServiceLocator()
                    ->get('Logger');
                $logger->warn(get_class().': object is of class '.get_class($v));
            }
        }
        $this->addVariants($defaults);
    }
    
    /*public function getVariantCounter() {
        return $this->variantCounter;
    }*/
    
    private function addVariants($defaults=array()) {
        #$this->variantCounter = 0;
        foreach($this->variants as $variant) {
            /* Example array
             * array(
                'name' => 'birthday',
                'attributes' => array(
                    'type'  => 'text',
                    'class'  => 'datepicker',
                ),
                'options' => array(
                    'label' => 'Birthday',
                ),
            )*/
            
            $productVariant = array();
            $productVariant['name'] = 'pv['.$variant->getId().']';
           
            switch(strtolower($variant->getType())) {
                case 'text':
                    $productVariant['attributes'] = array();
                    $productVariant['attributes']['type'] = $variant->getType();
                    $productVariant['attributes']['class'] = 'form-control form-element';
                    if(isset($defaults[$variant->getId()])) {
                        $productVariant['attributes']['value'] = $defaults[$variant->getId()];
                    }
                    
                    $productVariant['options'] = array();
                    $productVariant['options']['label'] = $variant->getName();
                    $productVariant['options']['label_attributes'] = array(
                            'class'  => 'media-object',
                        );
                    break;
                case 'date':
                    $productVariant['attributes'] = array();
                    $productVariant['attributes']['type'] = 'text';
                    $productVariant['attributes']['class'] = 'form-control form-element datepicker';
                    if(isset($defaults[$variant->getId()])) {
                        $productVariant['attributes']['value'] = $defaults[$variant->getId()];
                    }
                    
                    $productVariant['options'] = array();
                    $productVariant['options']['label'] = $variant->getName();
                    $productVariant['options']['label_attributes'] = array(
                            'class'  => 'media-object',
                        );
                    break;
                case 'select':
                    $productVariant['type'] = 'Zend\Form\Element\Select';
                    $options = array();
                    foreach($variant->getProductVariantValues() as $v) {
                        $selected = false;
                        
                        if(isset($defaults[\urlencode($variant->getName())]) &&  $v->getId() == $defaults[\urlencode($variant->getName())]) {
                            $selected = true;
                        }
                        $options[] = array(
                            'value' => $v->getId(),
                            'label' => $v->getValue(),
                            'selected' => $selected,
                        );
                    }
                    array_unshift($options, array(
                        'value' => 0,
                        'label' => 'select '.$variant->getName(),
                    ));
            
                    $productVariant['attributes'] = array();
                    $productVariant['attributes']['options'] = $options;
                    $productVariant['attributes']['class'] = 'form-control form-element';
                    
                    $productVariant['options'] = array();
                    $productVariant['options']['label'] = $variant->getName();
                    $productVariant['options']['label_attributes'] = array(
                            'class'  => 'media-object',
                        );
                    
                    $factory = new InputFactory(); 
                    $this->inputFilter->add($factory->createInput([ 
                        'name' => $productVariant['name'],
                        'filters' => array(
                            array('name' => 'Int'),
                        ),
                        'validators' => array(
                        ),
                    ])); 
                    
                    break;
                default:
                    error_log(get_class().': Don\'t know what to do with type '.$variant->getType());
                    break;
            }
           
            $this->add($productVariant);
            #$this->variantCounter++;
        }
    }
    
    public function getInputFilter()
    {
        $this->inputFilter = new InputFilter(); 
        $factory = new InputFactory();             

        $this->inputFilter->add($factory->createInput([ 
            'name' => 'Product_id', 
            'required' => true, 
            'filters' => array( 
                array('name' => 'Int'),
            ), 
            'validators' => array( 
            ), 
        ])); 

        $this->inputFilter->add($factory->createInput([ 
            'name' => 'participant_id', 
            'required' => true, 
            'filters' => array( 
                array('name' => 'Int'),
            ), 
            'validators' => array( 
            ), 
        ])); 

        $this->inputFilter->add($factory->createInput([ 
            'name' => 'agegroup_id', 
            'required' => true, 
            'filters' => array( 
                array('name' => 'Int'),
            ), 
            'validators' => array(
            ), 
        ])); 
        return $this->inputFilter; 
    }
}