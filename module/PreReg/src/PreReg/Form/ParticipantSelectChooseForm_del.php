<?php   

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreReg\Form;

use Zend\Form\Form;


class ParticipantSelectChooseForm_del extends Form
{
    public function __construct($name = null)
    {
        
        parent::__construct('ParticipantSelectChoose');
        
    }

    public function setVariants($variants) {
        foreach($variants as $v) {
            if(is_object($v)) {
                if(get_class($v) == 'PreReg\Model\Entity\ProductVariant') {
                    if(isset($v->order) && $v->order != 0) {
                        $this->variants[$v->order] = $v;
                    } else {
                        $this->variants[] = $v;
                    }
                } else {
                    error_log('object is of class '.get_class($v));
                }
                
            } else {
                error_log('ERROR: Unable to add ProductVariant to Form');
            }
        }
        error_log('got '.count($this->variants).' variants');
        $this->addVariants();
    }
    
    public function getVariantCounter() {
        return $this->variantCounter;
    }
    
    private function addVariants() {
        $this->variantCounter = 0;
        error_log('add '.count($this->variants).' variants');
        foreach($this->variants as $variant) {
            $formElement = array();
            $formElement['name'] = 'variant_'.$this->variantCounter;
            switch(strtolower($variant->type)) {
                case 'text':
                    $formElement['attributes'] = array();
                    $formElement['attributes']['type'] = $variant->type;
            
                    $formElement['options'] = array();
                    $formElement['options']['label'] = $variant->name;
                    break;
                case 'date':
                    $formElement['attributes'] = array();
                    $formElement['attributes']['type'] = 'text';
                    $formElement['attributes']['class'] = 'datepicker';
            
                    $formElement['options'] = array();
                    $formElement['options']['label'] = $variant->name;
                    break;
                case 'select':
                    $formElement['type'] = 'Zend\Form\Element\Select';
                    $options = array();
                    foreach($variant->getValues() as $v) {
                        $options[$v->id] = $v->value;
                    }
                    $formElement['attributes'] = array();
                    $formElement['attributes']['options'] = $options;
            
                    $formElement['options'] = array();
                    $formElement['options']['label'] = $variant->name;
                    break;
                default:
                    error_log('Don\'t know what to do with type '.$variant->type);
                    break;
            }
            
            $this->add($formElement);
            error_log('added '.$variant->name.' as '.$variant->type);
            
            $this->variantCounter++;
        }
    }
}