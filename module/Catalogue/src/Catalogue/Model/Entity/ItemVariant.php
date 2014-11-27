<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Catalogue\Model\Entity;

class ItemVariant extends Entity {
    protected $id;
    protected $name;
    protected $value;
    protected $Item_id;
    protected $ProductVariant_id;
    protected $ProductVariantValue_id;
    
    public function fromProductVariant(\Admin\Model\Entity\ProductVariant $ProductVariant) {
        $this->name = $ProductVariant->name;
        $this->ProductVariant_id = $ProductVariant->id;        
    }
    
    public function setItemId($id) {
        $this->Item_id = $id;
    }
    public function setValue(ProductVariantValue $value) {
        $this->value = $value->value;
        $this->ProductVariantValue_id = $value->id;
    }
}