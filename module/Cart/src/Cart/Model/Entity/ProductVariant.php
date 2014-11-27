<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cart\Model\Entity;

class ProductVariant extends Entity {
    protected $id;
    protected $Product_id;
    protected $order;
    protected $name;
    protected $type;
    protected $values;
    
    protected $inputFilter;
    
    public function __construct(array $options = null) {
        parent::__construct($options);
    }
    
    public function exchangeArray($data)
    {
        $this->id = (!empty($data['id'])) ? $data['id'] : null;
        $this->Product_id = (!empty($data['Product_id'])) ? $data['Product_id'] : null;
        $this->order  = (!empty($data['order'])) ? $data['order'] : null;
        $this->name  = (!empty($data['name'])) ? $data['name'] : null;
        $this->type  = (!empty($data['type'])) ? $data['type'] : null;
        parent::exchangeArray($data);
    }
    
    public function setValues($values) {
        $this->values = $values;
    }
    public function getValues() {
        return $this->values;
    }
}