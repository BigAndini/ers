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

            /*$inputFilter->add($factory->createInput(array(
                'name'     => 'validFrom',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name' => 'Callback',
                        'options' => array(
                            'messages' => array(
                                    \Zend\Validator\Callback::INVALID_VALUE => 'The valid from date needs to be in date format (yyyy-mm-dd hh:mm:ss)',
                            ),
                            'callback' => function($value, $context = array()) {
                                error_log('value: '.$value);
                                $Date = \DateTime::createFromFormat('Y-m-d H:i:s', $value);
                                if(!$Date) {
                                    return false;
                                } else {
                                    return true;
                                }
                            },
                        ),
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
            )));*/

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