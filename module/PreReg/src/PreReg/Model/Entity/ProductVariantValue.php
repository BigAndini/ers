<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreReg\Model\Entity;

class ProductVariantValue extends Entity {
    protected $id;
    protected $ProductVariant_id;
    protected $order;
    protected $value;
    
    public function exchangeArray($data) {
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
            'ProductVariant_id',
            'order',
            'value',
            'updated',
            'created'
        );
    }

    public function getId() {
        return $this->id;
    }
    
    public function getProductVariantId() {
        return $this->ProductVariant_id;
    }
    public function setProductVariantId($id) {
        $this->ProductVariant_id = $id;
    }
    
    public function getOrder() {
        return $this->order;
    }
    public function setOrder($order) {
        $this->order = $order;
    }
    
    public function getValue() {
        return $this->value;
    }
    public function setValue($value) {
        $this->value = $value;
    }
    
}