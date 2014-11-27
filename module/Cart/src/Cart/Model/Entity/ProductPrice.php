<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cart\Model\Entity;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class ProductPrice extends Entity {
    /*
     * The Product_id where this Price belongs to.
     */
    protected $Product_id;
    
    /*
     * The charge of this price in euro
     */
    protected $charge;
    
    /*
     * array which holds all PriceLimits for this Price
     */
    protected $limits;
    
    /* can have values: 0, 1 or 2
     * 0: a price that is not valid anymore
     * 1: a price that is valid at the moment
     * 2: a price that will be valid in future
     */
    protected $valid; 
    
    protected $inputFilter;


    public function __construct(array $options = null) {
        parent::__construct($options);
    }
    
    public function exchangeArray($data)
    {
        if(is_object($data)) {
            $this->Product_id = (!empty($data->Product_id)) ? $data->Product_id : null;
            #$this->setValidfrom($data->validFrom);
            $this->charge = (!empty($data->charge)) ? $data->charge : null;
        } elseif(is_array($data)) {
            $this->Product_id = (!empty($data['Product_id'])) ? $data['Product_id'] : null;
            $this->charge  = (!empty($data['charge'])) ? $data['charge'] : null;    
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
                'name'     => 'validFrom',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => '\Zend\I18n\Validator\DateTime',
                        'options' => array(
                            'locale' => 'de_DE',
                            'dateType' => \IntlDateFormatter::SHORT,
                            'timeType' => \IntlDateFormatter::SHORT,
                        ),
                        #'format'  => 'd.m.Y H:i',
                    ),
                    array(
                        'name' => 'Callback',
                        'options' => array(
                            'messages' => array(
                                    \Zend\Validator\Callback::INVALID_VALUE => 'Just logging some information',
                            ),
                            'callback' => function($value, $context = array()) {
                                error_log('given value: '.$value);
                                return true;
                            },
                        ),
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