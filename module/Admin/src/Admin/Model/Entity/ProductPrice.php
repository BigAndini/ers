<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Model\Entity;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class ProductPrice extends Entity {
    protected $Product_id;
    protected $charge;
    
    protected $inputFilter;


    public function __construct(array $options = null) {
        parent::__construct($options);
    }
    
    public function exchangeArray($data)
    {
        if(is_object($data)) {
            $this->Product_id = (!empty($data->Product_id)) ? $data->Product_id : null;
            $this->charge = (!empty($data->charge)) ? $data->charge : null;
            if(isset($data->charge) && is_numeric($data->charge)) {
                $this->charge  = $data->charge;
            }
        } elseif(is_array($data)) {
            error_log(var_export($data, true));
            $this->Product_id = (!empty($data['Product_id'])) ? $data['Product_id'] : null;
            if(isset($data['charge']) && is_numeric($data['charge'])) {
                $this->charge  = $data['charge'];
            }
        }
        
        parent::exchangeArray($data);
    }
    
    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory     = new InputFactory();

            $inputFilter->add($factory->createInput(array(
                'name'     => 'id',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'Product_id',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'Digits',
                    ),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'charge',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                    new \Zend\I18n\Filter\NumberFormat("en_US"),
                ),
                'validators' => array(
                    array(
                        'name'    => 'Float',
                    ),
                ),
            )));
            
            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}