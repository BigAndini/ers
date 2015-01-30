<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreReg\Model\Entity;

class ItemVariant extends Entity {
    protected $id;
    protected $name;
    protected $value;
    protected $Item_id;
    protected $ProductVariant_id;
    protected $ProductVariantValue_id;
    
    public function fromProductVariant(\PreReg\Model\Entity\ProductVariant $ProductVariant) {
        $this->name = $ProductVariant->name;
        $this->ProductVariant_id = $ProductVariant->id;        
    }
    
    public function __sleep() {
        return array(
            'id',
            'name',
            'value',
            'Item_id',
            'ProductVariant_id',
            'ProductVariantValue_id',
            'updated',
            'created',
        );
    }
    
    public function exchangeArray($data) {
        if(is_object($data)) {
            $this->id = (!empty($data->id)) ? $data->id : null;
            $this->name = (!empty($data->name)) ? $data->name : null;
            $this->value = (!empty($data->value)) ? $data->value : null;
            $this->Item_id = (!empty($data->Item_id)) ? $data->Item_id : null;
            $this->ProductVariant_id = (!empty($data->ProductVariant_id)) ? $data->ProductVariant_id : null;
            $this->ProductVariantValue_id = (!empty($data->ProductVariantValue_id)) ? $data->ProductVariantValue_id : null;
        } elseif(is_array($data)) {
            $this->id  = (!empty($data['id'])) ? $data['id'] : null;
            $this->name  = (!empty($data['name'])) ? $data['name'] : null;
            $this->value  = (!empty($data['value'])) ? $data['value'] : null;
            $this->Item_id  = (!empty($data['Item_id'])) ? $data['Item_id'] : null;
            $this->ProductVariant_id  = (!empty($data['ProductVariant_id'])) ? $data['ProductVariant_id'] : null;
            $this->ProductVariantValue_id  = (!empty($data['ProductVariantValue_id'])) ? $data['ProductVariantValue_id'] : null;
        } else {
            error_log('exchangeArray: given data is either an object nor an array!');
        }
        parent::exchangeArray($data);
    }
    
    public function setItemId($id) {
        $this->Item_id = $id;
    }
    public function setValue(ProductVariantValue $value) {
        $this->value = $value->value;
        $this->ProductVariantValue_id = $value->id;
    }
    public function getName() {
        return $this->name;
    }
    public function setName($value) {
        $this->name = $value;
    }
    
    public function getProductVariantValueId() {
        return $this->ProductVariantValue_id;
    }
    public function setProductVariantValueId($id) {
        $this->ProductVariantValue_id = $id;
    }
}