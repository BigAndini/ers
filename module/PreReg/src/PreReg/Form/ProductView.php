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
use ErsBase\Entity;
use Zend\Session\Container;

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
            'value' => '',
            'attributes' => array(
                'class' => 'form-control form-element',
            ),
            'options' => array(
                'label' => _('This ticket belongs to:'),
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
            'value' => '',
            'attributes' => array(
                'class' => 'checkbox-inline',
            ),
            'options' => array(
                'label' => _('Agegroup'),
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
                'value' => _('Go'),
                'id' => 'submitbutton',
                'class' => 'btn btn-primary',
            ),
        ));
        
        $this->getInputFilter();
    }

    public function setVariants($variants, $defaults = array(), $package_info = array()) {
        $variant_count = count($variants);
        $variant_add = 1;
        foreach($variants as $v) {
            if(is_object($v) && $v instanceof Entity\ProductVariant) {
                if($v->getPosition() != null && $v->getPosition() != 0) {
                    $this->variants[$v->getPosition()] = $v;
                } else {
                    # Make sure the variants without order number or 
                    # order number == 0 will be shown last.
                    $this->variants[$variant_count+$variant_add] = $v;
                    $variant_add++;
                }
            } else {
                $logger = $this->getServiceLocator()->get('Logger');
                $logger->warn(get_class().': object is of class '.get_class($v));
            }
        }
        $this->addVariants($defaults, $package_info);
    }
    
    private function addVariants($defaults = array(), $package_info = array()) {
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
                        if($v->getDisabled()) {
                            $disabled = true;
                            $value = $v->getValue().' (sold out)';
                        } else {
                            $disabled = false;
                            $value = $v->getValue();
                        }
                        $options[] = array(
                            'value' => $v->getId(),
                            'label' => $value,
                            'selected' => $selected,
                            'disabled' => $disabled,
                        );
                    }
                    if($package_info[$variant->getId()]) {
                        $options[] = array(
                            'value' => 0,
                            'label' => _('no').' '.$variant->getName(),
                        );
                    }
                    array_unshift($options, array(
                        'value' => '',
                        'label' => '',
                    ));
            
                    $productVariant['attributes'] = array();
                    $productVariant['attributes']['options'] = $options;
                    $productVariant['attributes']['class'] = 'form-control form-element';
                    
                    $productVariant['options'] = array();
                    $productVariant['options']['label'] = $variant->getName();
                    $productVariant['options']['label_attributes'] = array(
                            'class'  => 'media-object',
                        );
                    
                    break;
                default:
                    throw new \Exception(get_class().': Don\'t know what to do with type '.$variant->getType());
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
                array(
                    'name' => 'Callback',
                    'options' => array(
                        'messages' => array(
                            \Zend\Validator\Callback::INVALID_VALUE => _('Please select a person.'),
                        ),
                        'callback' => function($value, $context=array()) {
                            if(is_numeric($value)) {
                                return true;
                            }                
                            if(isset($context['agegroup_id']) && is_numeric($context['agegroup_id'])) {
                                return true;
                            }
                            

                            return false;
                        },

                    ),
                ),
                array(
                    'name' => 'Callback',
                    'options' => array(
                        'messages' => array(
                            \Zend\Validator\Callback::INVALID_VALUE => _('Unable to add personalized product to person without birthdate. Please add date of birth in My Person list.'),
                        ),
                        'callback' => function($value, $context=array()) {
                            $cartContainer = new Container('cart');
                            $participant = $cartContainer->order->getParticipantById($value);
                            if(is_object($participant)) {
                                if(!$participant->getBirthday() instanceof \DateTime) {
                                    return false;
                                } else {
                                    return true;
                                }
                            } else {
                                # this is not a personalized product
                                return true;
                            }
                        },

                    ),
                ),
            ), 
        ])); 

        $this->inputFilter->add($factory->createInput([ 
            'name' => 'agegroup_id', 
            'required' => true, 
            'filters' => array( 
                array('name' => 'Int'),
            ), 
            'validators' => array(
                array(
                    'name' => 'Callback',
                    'options' => array(
                        'messages' => array(
                            \Zend\Validator\Callback::INVALID_VALUE => _('Please select a agegroup.'),
                        ),
                        'callback' => function($value, $context=array()) {
                            if(is_numeric($value)) {
                                return true;
                            }
                            if(isset($context['participant_id']) && is_numeric($context['participant_id'])) {
                                return true;
                            }

                            return false;
                        },

                    ),
                ),
            ), 
        ])); 
        $this->inputFilter->add($factory->createInput([ 
            'name' => 'pv', 
            'required' => false, 
            'filters' => array( 
                array("name" => "Callback", "options" => array(
                    "callback" => function($values) {
                        if(!is_array($values)) {
                            return array();
                        }
                        $strip = new \Zend\Filter\StripTags();
                        $trim = new \Zend\Filter\StringTrim();
                        foreach($values as $key => $value) {
                            #$value = $int->filter($value);
                            $value = $strip->filter($value);
                            $value = $trim->filter($value);
                            $values[$key] = $value;
                        }
                        return $values;
                    })),
            ), 
            'validators' => array(
                array(
                    'name' => 'Callback',
                    'options' => array(
                        'messages' => array(
                            \Zend\Validator\Callback::INVALID_VALUE => _('Please select a product variant.'),
                        ),
                        'callback' => function($values, $context=array()) {
                            foreach($values as $key => $value) {
                                if($value == '') {
                                    return false;
                                }
                                if(!is_numeric($value)) {
                                    return false;
                                }
                            }
                            return true;
                        },

                    ),
                ),
            ), 
        ])); 
        return $this->inputFilter; 
    }
}