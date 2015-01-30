<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreReg\Model\Entity;

class ProductVariant extends Entity {
    protected $id;
    protected $Product_id;
    protected $order;
    protected $name;
    protected $type;
    protected $preselection;
    protected $values;
    
    protected $inputFilter;
    
    public function __construct(array $options = null) {
        parent::__construct($options);
    }
    
    public function exchangeArray($data)
    {
        foreach($data as $k => $v) {
            if(property_exists(get_class($this), $k)) {
                $this->$k  = (!empty($v)) ? $v : null;
            } else {
                error_log(get_class($this).': Unable to find property '.$k);
            }
        }
        parent::exchangeArray($data);
    }
    
    public function __sleep() {
        return array(
            'id',
            'Product_id',
            'order',
            'name',
            'type',
            'preselection',
            'values',
            'updated',
            'created',
        );
    }
    
    public function getId() {
        return $this->id;
    }
    public function getProductId() {
        return $this->Product_id;
    }
    public function setProductId($id) {
        $this->Product_id = $id;
    }
    
    public function getName() {
        return $this->name;
    }
    public function setName($name) {
        $this->name = $name;
    }
    
    public function getType() {
        return $this->type;
    }
    public function setType($type) {
        $this->type = $type;
    }

    public function setValues($values) {
        $this->values = $values;
    }
    public function addValue(ProductVariantValue $value) {
        $this->values[] = $value;
    }
    public function getValues() {
        return $this->values;
    }
}