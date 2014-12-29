<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Order\Model\Entity;

class Item extends Entity {
    /*
     * database fields
     */
    protected $id;
    protected $Product_id;
    protected $Package_id;
    protected $Package_Order_id;
    protected $Barcode_id;
    protected $name;
    protected $shortDescription;
    protected $longDescription;
    protected $price;
    protected $amount;
    protected $info;
    protected $status;
    
    /*
     * other storage
     */
    protected $variants;

    
    public function fromProduct(\Admin\Model\Entity\Product $Product) {
        $this->Product_id = $Product->id;
        $this->name = $Product->name;
        foreach($Product->getVariants() as $variant) {
            $ItemVariant = new ItemVariant();
            $this->addVariant($ItemVariant->fromProductVariant($variant));
        }
    }
    
    public function setVariantValue($variant, $value) {
        if(is_object($value) && get_class($v) === 'Entity\ProductVariantValue') {
            # get the value out of the object and set it for the correct variant
        } else {
            # in this case value should be a string. Maybe more checks are needed.
            # set value for this variant.
        }
    }
    
    public function setItemVariants(array $Variants) {
        foreach($Variants as $v) {
            if(get_class($v) === 'Entity\ItemVariant') {
                $this->addItemVariant($v);
            }
        }
    }
    public function getItemVariants() {
        return $this->variants;
    }
    public function addItemVariant(Entity\ItemVariant $Variant) {
        if(get_class($Variant) === 'Entity\ItemVariant') {
            $this->variants[] = $Variant;
        }
    }
    public function getItemVariant($criteria) {
        foreach($this->variants as $v) {
            if(\strtolower($v->name) === \strtolower($criteria)) {
                return $v;
            }
        }
        return false;
    }
}